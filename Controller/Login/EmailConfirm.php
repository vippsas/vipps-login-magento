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
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\AccessTokenProvider;
use Vipps\Login\Model\RedirectUrlResolver;
use Vipps\Login\Model\VippsAccountManagement;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

/**
 * Class EmailConfirm
 * @package Vipps\Login\Controller\Login
 */
class EmailConfirm extends Action
{
    /**
     * @var VippsAccountManagement
     */
    private $vippsAccountManagement;

    /**
     * @var SessionManagerInterface|Session
     */
    private $sessionManager;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var VippsAddressManagementInterface
     */
    private $vippsAddressManagement;

    /**
     * @var AccessTokenProvider
     */
    private $accessTokenProvider;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RedirectUrlResolver
     */
    private $redirectUrlResolver;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * EmailConfirm constructor.
     *
     * @param Context $context
     * @param VippsAccountManagement $vippsAccountManagement
     * @param SessionManagerInterface $sessionManager
     * @param CustomerRegistry $customerRegistry
     * @param ManagerInterface $messageManager
     * @param AccessTokenProvider $accessTokenProvider
     * @param UserInfoCommand $userInfoCommand
     * @param VippsAddressManagementInterface $vippsAddressManagement
     * @param RedirectUrlResolver $redirectUrlResolver
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        VippsAccountManagement $vippsAccountManagement,
        SessionManagerInterface $sessionManager,
        CustomerRegistry $customerRegistry,
        ManagerInterface $messageManager,
        AccessTokenProvider $accessTokenProvider,
        UserInfoCommand $userInfoCommand,
        VippsAddressManagementInterface $vippsAddressManagement,
        RedirectUrlResolver $redirectUrlResolver,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->sessionManager = $sessionManager;
        $this->customerRegistry = $customerRegistry;
        $this->messageManager = $messageManager;
        $this->vippsAddressManagement = $vippsAddressManagement;
        $this->accessTokenProvider = $accessTokenProvider;
        $this->userInfoCommand = $userInfoCommand;
        $this->logger = $logger;
        $this->redirectUrlResolver = $redirectUrlResolver;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $key = $this->getRequest()->getParam('key');

        $redirect = $this->resultRedirectFactory->create();
        try {
            $vippsCustomer = $this->vippsAccountManagement->confirm($id, $key);
            if ($vippsCustomer) {
                $customer = $this->customerRegistry->retrieve($vippsCustomer->getCustomerEntityId());

                $this->sessionManager->setCustomerAsLoggedIn($customer);
                $this->sessionManager->regenerateId();
                if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                    $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
                }

                $this->messageManager->addSuccessMessage(__('Your account was successfully confirmed.'));

                if ($accessToken = $this->accessTokenProvider->get()) {
                    $userInfo = $this->userInfoCommand->execute($accessToken);
                    $this->vippsAddressManagement->apply($userInfo, $vippsCustomer, $customer->getDataModel());
                }

                return $redirect->setUrl(
                    $this->redirectUrlResolver->getRedirectUrl()
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('An error occurred during email confirmation.'));
        }

        return $redirect->setPath('/');
    }
}
