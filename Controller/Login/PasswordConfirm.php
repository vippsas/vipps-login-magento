<?php
/**
 * Copyright 2019 Vipps
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

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\AccessTokenProvider;

/**
 * Class PasswordConfirm
 * @package Vipps\Login\Controller\Login
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PasswordConfirm extends Action
{
    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var VippsAccountManagementInterface
     */
    private $vippsAccountManagement;

    /**
     * @var AccessTokenProvider
     */
    private $accessTokenProvider;

    /**
     * PasswordConfirm constructor.
     *
     * @param Context $context
     * @param UserInfoCommand $userInfoCommand
     * @param SessionManagerInterface $customerSession
     * @param SerializerInterface $serializer
     * @param AccountManagementInterface $customerAccountManagement
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $resultRawFactory
     * @param AccountRedirect $accountRedirect
     * @param ScopeConfigInterface $scopeConfig
     * @param VippsAccountManagementInterface $vippsAccountManagement
     * @param AccessTokenProvider $accessTokenProvider
     */
    public function __construct(
        Context $context,
        UserInfoCommand $userInfoCommand,
        SessionManagerInterface $customerSession,
        SerializerInterface $serializer,
        AccountManagementInterface $customerAccountManagement,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        AccountRedirect $accountRedirect,
        ScopeConfigInterface $scopeConfig,
        VippsAccountManagementInterface $vippsAccountManagement,
        AccessTokenProvider $accessTokenProvider
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->userInfoCommand = $userInfoCommand;
        $this->serializer = $serializer;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->accountRedirect = $accountRedirect;
        $this->scopeConfig = $scopeConfig;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->accessTokenProvider = $accessTokenProvider;
    }

    /**
     * Login registered users and initiate a session.
     *
     * Expects a POST. ex for JSON {"username":"user@magento.com", "password":"userpassword"}
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $credentials = null;
        $httpBadRequestCode = 400;

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = $this->serializer->unserialize($this->getRequest()->getContent());
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        if (!$this->isValid($credentials)) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];

        try {
            $magentoCustomer = $this->customerAccountManagement->authenticate(
                $credentials['username'],
                $credentials['password']
            );

            try {
                $userInfo = $this->userInfoCommand->execute($this->accessTokenProvider->get());
            } catch (\Throwable $e) {
                return $resultRaw->setHttpResponseCode($httpBadRequestCode);
            }

            $this->customerSession->setCustomerDataAsLoggedIn($magentoCustomer);
            $this->customerSession->regenerateId();

            $redirectRoute = $this->accountRedirect->getRedirectCookie();
            if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                $this->accountRedirect->clearRedirectCookie();
            }

            $this->vippsAccountManagement->link($userInfo, $magentoCustomer);

        } catch (EmailNotConfirmedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (InvalidEmailOrPasswordException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Throwable $e) {
            $response = [
                'errors' => true,
                'message' => __('Invalid login or password.')
            ];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * @param $credentials
     *
     * @return bool
     */
    private function isValid($credentials)
    {
        if (!$credentials ||
            !array_key_exists('username', $credentials) ||
            !array_key_exists('password', $credentials) ||
            $this->getRequest()->getMethod() !== 'POST' ||
            !$this->getRequest()->isXmlHttpRequest()
        ) {
            return false;
        }

        return true;
    }
}
