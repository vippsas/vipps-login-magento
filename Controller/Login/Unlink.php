<?php
/**
 * Copyright 2019 Vipps
 *
 *    Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 *    documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 *    the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 *    and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 *    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 *    TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 *    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 *    CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 *    IN THE SOFTWARE
 */

declare(strict_types=1);

namespace Vipps\Login\Controller\Login;

use Vipps\Login\Model\VippsAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Unlink
 * @package Vipps\Login\Controller\Login
 */
class Unlink extends Action
{
    /**
     * @var VippsAccountManagement
     */
    private $vippsAccountManagement;

    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * EmailConfirm constructor.
     *
     * @param Context $context
     * @param VippsAccountManagement $vippsAccountManagement
     * @param SessionManagerInterface $customerSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        VippsAccountManagement $vippsAccountManagement,
        SessionManagerInterface $customerSession,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please, login first.'));
        }

        try {
            $customer = $this->customerSession->getCustomer();
            $this->vippsAccountManagement->unlink($customer->getDataModel());
            $this->messageManager->addSuccessMessage(__('Your account was successfully unlinked.'));
            return $redirect->setPath('customer/account');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred during unbinding accounts. Please, try again later.')
            );
        }

        return $redirect->setPath('/');
    }
}
