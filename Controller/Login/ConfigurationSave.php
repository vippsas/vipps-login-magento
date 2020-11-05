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

use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;

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
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * ConfigurationSave constructor.
     *
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param SessionManagerInterface $customerSession
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param Validator $formKeyValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        SessionManagerInterface $customerSession,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        Validator $formKeyValidator,
        LoggerInterface $logger
    ) {
        parent::__construct($customerSession, $request, $logger);
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->request = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();

        $refererUrl = $this->redirect->getRefererUrl();
        $redirect->setPath($refererUrl);

        if (!$this->canProcess() || !$this->formKeyValidator->validate($this->getRequest())) {
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
