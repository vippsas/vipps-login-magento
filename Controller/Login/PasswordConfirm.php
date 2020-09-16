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

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
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
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\AccessTokenProvider;
use Vipps\Login\Model\RedirectUrlResolver;

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var VippsAccountManagementInterface
     */
    private $vippsAccountManagement;

    /**
     * @var VippsAddressManagementInterface
     */
    private $vippsAddressManagement;

    /**
     * @var AccessTokenProvider
     */
    private $accessTokenProvider;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var RedirectUrlResolver
     */
    private $redirectUrlResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

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
     * @param RedirectUrlResolver $redirectUrlResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param VippsAccountManagementInterface $vippsAccountManagement
     * @param AccessTokenProvider $accessTokenProvider
     * @param UrlInterface $url
     * @param VippsAddressManagementInterface $vippsAddressManagement
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        UserInfoCommand $userInfoCommand,
        SessionManagerInterface $customerSession,
        SerializerInterface $serializer,
        AccountManagementInterface $customerAccountManagement,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        RedirectUrlResolver $redirectUrlResolver,
        ScopeConfigInterface $scopeConfig,
        VippsAccountManagementInterface $vippsAccountManagement,
        AccessTokenProvider $accessTokenProvider,
        UrlInterface $url,
        VippsAddressManagementInterface $vippsAddressManagement,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->userInfoCommand = $userInfoCommand;
        $this->serializer = $serializer;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->scopeConfig = $scopeConfig;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->vippsAddressManagement = $vippsAddressManagement;
        $this->accessTokenProvider = $accessTokenProvider;
        $this->url = $url;
        $this->redirectUrlResolver = $redirectUrlResolver;
        $this->logger = $logger;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
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
            $this->logger->critical($e);
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        if (!$this->isValid($credentials)) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'message' => __('You are logged in.')
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
            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }

            $response['redirectUrl'] = $this->redirectUrlResolver->getRedirectUrl();

            $vippsCustomer = $this->vippsAccountManagement->link($userInfo, $magentoCustomer);

            $this->vippsAddressManagement->apply($userInfo, $vippsCustomer, $magentoCustomer);
        } catch (EmailNotConfirmedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (LocalizedException $e) {
            $this->logger->error($e);
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Throwable $e) {
            $this->logger->critical($e);
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
        if ($credentials &&
            array_key_exists('username', $credentials) &&
            array_key_exists('password', $credentials) &&
            $this->getRequest()->getMethod() === 'POST' &&
            $this->getRequest()->isXmlHttpRequest()
        ) {
            return true;
        }

        return false;
    }
}
