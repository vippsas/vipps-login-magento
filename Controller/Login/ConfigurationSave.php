<?php
/**
 * Copyright 2020 Vipps
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 * TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Vipps\Login\Controller\Login;

use Psr\Log\LoggerInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * Class Unlink
 * @package Vipps\Login\Controller\Login
 */
class ConfigurationSave extends AccountBase
{
    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConfigurationSave constructor.
     *
     * @param Context $context
     * @param SessionManagerInterface $customerSession
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param Validator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $customerSession,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        Validator $formKeyValidator,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $customerSession);
        $this->messageManager = $messageManager;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $refererUrl = $this->_redirect->getRefererUrl();
        $redirect->setPath($refererUrl);

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $redirect;
        }

        try {
            $customer = $this->customerSession->getCustomer();

            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer->getDataModel());
            $syncType = $this->getRequest()->getParam('sync_address_mode');

            $vippsCustomer->setSyncAddressMode($syncType);
            $this->vippsCustomerRepository->save($vippsCustomer);

            $this->messageManager->addSuccessMessage(__('Updated successfully'));
            return $redirect;
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred when applying settings. Please, try again later.')
            );
            $this->logger->critical($e);
        }

        return $redirect;
    }
}
