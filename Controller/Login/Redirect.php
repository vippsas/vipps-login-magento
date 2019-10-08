<?php
/**
 * Copyright 2018 Vipps
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 *  documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 *  the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 *  and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 *  TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 *  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 *  CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 *  IN THE SOFTWARE.
 */
namespace Vipps\Login\Controller\Login;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect as MagentoRedirect;
use Magento\Framework\App\ResponseInterface;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Gateway\Command\TokenCommand;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\Customer\AccountsProvider;
use Vipps\Login\Model\Customer\Creator;
use Vipps\Login\Model\Customer\TrustedAccountsLocator;
use Vipps\Login\Model\StateKey;
use Vipps\Login\Model\TokenProviderInterface;

/**
 * Class Redirect
 * @package Vipps\Login\Controller\Login
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Redirect extends Action
{
    /**
     * @var
     */
    private $customerRegistry;

    /**
     * @var SessionManagerInterface|Session
     */
    private $sessionManager;

    /**
     * @var TokenCommand
     */
    private $tokenCommand;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * @var Creator
     */
    private $creator;

    /**
     * @var TrustedAccountsLocator
     */
    private $trustedAccountsLocator;

    /**
     * @var TokenProviderInterface
     */
    private $openIDtokenProvider;

    /**
     * @var StateKey
     */
    private $stateKey;

    /**
     * @var AccountsProvider
     */
    private $accountsProvider;
    /**
     * @var VippsAccountManagementInterface
     */
    private $vippsAccountManagement;

    /**
     * @var VippsAddressManagementInterface
     */
    private $vippsAddressManagement;

    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param CustomerRegistry $customerRegistry
     * @param SessionManagerInterface $sessionManager
     * @param TokenCommand $tokenCommand
     * @param TrustedAccountsLocator $trustedAccountsLocator
     * @param TokenProviderInterface $openIDtokenProvider
     * @param UserInfoCommand $userInfoCommand
     * @param Creator $creator
     * @param StateKey $stateKey
     * @param AccountsProvider $accountsProvider
     * @param VippsAccountManagementInterface $vippsAccountManagement
     * @param VippsAddressManagementInterface $vippsAddressManagement
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        CustomerRegistry $customerRegistry,
        SessionManagerInterface $sessionManager,
        TokenCommand $tokenCommand,
        TrustedAccountsLocator $trustedAccountsLocator,
        TokenProviderInterface $openIDtokenProvider,
        UserInfoCommand $userInfoCommand,
        Creator $creator,
        StateKey $stateKey,
        AccountsProvider $accountsProvider,
        VippsAccountManagementInterface $vippsAccountManagement,
        VippsAddressManagementInterface $vippsAddressManagement
    ) {
        parent::__construct($context);
        $this->customerRegistry = $customerRegistry;
        $this->sessionManager = $sessionManager;
        $this->tokenCommand = $tokenCommand;
        $this->trustedAccountsLocator = $trustedAccountsLocator;
        $this->openIDtokenProvider = $openIDtokenProvider;
        $this->stateKey = $stateKey;
        $this->accountsProvider = $accountsProvider;
        $this->userInfoCommand = $userInfoCommand;
        $this->creator = $creator;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->vippsAddressManagement = $vippsAddressManagement;
    }

    /**
     * @return ResponseInterface|MagentoRedirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $code = $this->_request->getParam('code');
        $state = $this->_request->getParam('state');

        try {
            if (!$this->stateKey->isValid($state)) {
                $resultRedirect->setUrl('/');
                return $resultRedirect;
            }

            $tokenData = $this->tokenCommand->execute($code);
            $this->storeToken($tokenData);

            $customer = $this->getTrustedAccount($tokenData['decoded_id_token']->phone_number);

            if ($customer) {
                $this->sessionManager->setCustomerAsLoggedIn($customer);
                $resultRedirect->setPath('customer/account');
                return $resultRedirect;
            }

            $customers = $this->accountsProvider->retrieveByPhoneOrEmail(
                $tokenData['decoded_id_token']->phone_number,
                $tokenData['decoded_id_token']->email
            );

            if ($customers) {
                $resultRedirect->setPath('vipps/login/verification');
                return $resultRedirect;
            }

            $userInfo = $this->userInfoCommand->execute();
            $customer = $this->creator->create($userInfo);
            $vippsCustomer = $this->vippsAccountManagement->link($userInfo, $customer);
            $this->vippsAddressManagement->fetchAddresses($userInfo, $vippsCustomer);

            $this->sessionManager
                ->setCustomerAsLoggedIn($this->customerRegistry->retrieveByEmail($customer->getEmail()));
            $resultRedirect->setPath('customer/account');
            return $resultRedirect;
        } catch (\Throwable $t) {
            $resultRedirect->setPath('vipps/login/error');
            return $resultRedirect;
        }
    }

    /**
     * @param $tokenData
     */
    private function storeToken($tokenData)
    {
        $this->sessionManager->setData('id_token', $tokenData['id_token']);
        $this->sessionManager->setData('decoded_id_token', $tokenData['decoded_id_token']);
        $this->sessionManager->setData('access_token', $tokenData['access_token']);
    }

    /**
     * @param $phoneNumber
     *
     * @return \Magento\Customer\Model\Customer|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTrustedAccount($phoneNumber)
    {
        $trustedAccounts = $this->trustedAccountsLocator->getList($phoneNumber);

        if ($trustedAccounts->getTotalCount() > 0) {
            $customerData = $trustedAccounts->getItems()[0];
            return $this->customerRegistry->retrieveByEmail($customerData->getEmail());
        }

        return null;
    }
}
