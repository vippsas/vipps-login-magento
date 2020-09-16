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

namespace Vipps\Login\Controller\Login\Redirect\Action;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Gateway\Command\UserInfoCommand;

/**
 * Class Bind
 * @package Vipps\Login\Controller\Login\Redirect\Action
 */
class Bind implements ActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var VippsAddressManagementInterface
     */
    private $vippsAddressManagement;

    /**
     * @var VippsAccountManagementInterface
     */
    private $vippsAccountManagement;

    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Bind constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param VippsAddressManagementInterface $vippsAddressManagement
     * @param VippsAccountManagementInterface $vippsAccountManagement
     * @param SessionManagerInterface $customerSession
     * @param UserInfoCommand $userInfoCommand
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        VippsAddressManagementInterface $vippsAddressManagement,
        VippsAccountManagementInterface $vippsAccountManagement,
        SessionManagerInterface $customerSession,
        UserInfoCommand $userInfoCommand,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->vippsAddressManagement = $vippsAddressManagement;
        $this->customerSession = $customerSession;
        $this->userInfoCommand = $userInfoCommand;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * @param $token
     *
     * @return mixed
     * @throws \Exception
     */
    public function execute($token)
    {
        if (!$this->customerSession->isLoggedIn()) {
            return false;
        }

        $resultRedirect = $this->redirectFactory->create();
        try {
            /** @var Customer $customer */
            $customerModel = $this->customerSession->getCustomer();
            $customer = $customerModel->getDataModel();
            $userInfo = $this->userInfoCommand->execute($token['access_token']);
            $vippsCustomer = $this->vippsAccountManagement->link($userInfo, $customer);

            $this->vippsAddressManagement->apply($userInfo, $vippsCustomer, $customer);

            $this->messageManager->addSuccessMessage(__('Your account was successfully linked with Vipps.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e);
            $this->logger->critical($e);
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred during linking accounts.')
            );
            $this->logger->critical($e);
        } finally {
            $resultRedirect->setPath('customer/account');
        }

        return $resultRedirect;
    }
}
