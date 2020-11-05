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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Psr\Log\LoggerInterface;
use Vipps\Login\Model\VippsAccountManagement;

/**
 * Class Unlink
 * @package Vipps\Login\Controller\Login
 */
class Unlink extends AccountBase
{
    /**
     * @var VippsAccountManagement
     */
    private $vippsAccountManagement;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * Unlink constructor.
     *
     * @param SessionManagerInterface $customerSession
     * @param RedirectInterface $redirect
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param LoggerInterface $logger
     * @param VippsAccountManagement $vippsAccountManagement
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        SessionManagerInterface $customerSession,
        RedirectInterface $redirect,
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        LoggerInterface $logger,
        VippsAccountManagement $vippsAccountManagement,
        ManagerInterface $messageManager
    ) {
        parent::__construct($customerSession, $request, $logger);
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $refererUrl = $this->redirect->getRefererUrl();
        $redirect->setPath($refererUrl);

        try {
            $customer = $this->customerSession->getCustomer();
            $this->vippsAccountManagement->unlink($customer->getDataModel());
            $this->messageManager->addSuccessMessage(__('Your account was successfully unlinked.'));
        } catch (\Throwable $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred during unbinding accounts. Please, try again later.')
            );
        }

        return $redirect;
    }
}
