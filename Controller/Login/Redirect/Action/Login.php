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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\Customer\TrustedAccountsLocator;
use Vipps\Login\Model\RedirectUrlResolver;
use Psr\Log\LoggerInterface;

/**
 * Class Login
 * @package Vipps\Login\Controller\Login\Redirect\Action
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login implements ActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var SessionManagerInterface|Session
     */
    private $sessionManager;

    /**
     * @var TrustedAccountsLocator
     */
    private $trustedAccountsLocator;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * @var VippsAddressManagementInterface
     */
    private $vippsAddressManagement;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RedirectUrlResolver
     */
    private $redirectUrlResolver;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * Login constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param SessionManagerInterface $sessionManager
     * @param TrustedAccountsLocator $trustedAccountsLocator
     * @param CustomerRegistry $customerRegistry
     * @param UserInfoCommand $userInfoCommand
     * @param VippsAddressManagementInterface $vippsAddressManagement
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param RedirectUrlResolver $redirectUrlResolver
     * @param ManagerInterface $messageManager
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        SessionManagerInterface $sessionManager,
        TrustedAccountsLocator $trustedAccountsLocator,
        CustomerRegistry $customerRegistry,
        UserInfoCommand $userInfoCommand,
        VippsAddressManagementInterface $vippsAddressManagement,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        RedirectUrlResolver $redirectUrlResolver,
        ManagerInterface $messageManager,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        LoggerInterface $logger
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->sessionManager = $sessionManager;
        $this->trustedAccountsLocator = $trustedAccountsLocator;
        $this->customerRegistry = $customerRegistry;
        $this->userInfoCommand = $userInfoCommand;
        $this->vippsAddressManagement = $vippsAddressManagement;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->logger = $logger;
        $this->redirectUrlResolver = $redirectUrlResolver;
        $this->messageManager = $messageManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @param $token
     *
     * @return bool|Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute($token)
    {
        $customer = $this->getCustomerForLogin($token);
        if ($customer) {
            $redirect = $this->redirectFactory->create();
            try {
                $userInfo = $this->userInfoCommand->execute($token['access_token']);

                $this->sessionManager->setCustomerAsLoggedIn($customer);
                $this->sessionManager->regenerateId();
                if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                    $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
                }

                $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer->getDataModel());

                $this->vippsAddressManagement->apply($userInfo, $vippsCustomer, $customer->getDataModel());

                $redirect = $this->redirectFactory->create();
                $redirect->setUrl(
                    $this->redirectUrlResolver->getRedirectUrl()
                );
                return $redirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e);
                $this->logger->critical($e);
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }

            return $redirect;
        }

        return false;
    }

    /**
     * @param array $token
     *
     * @return Customer|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomerForLogin($token)
    {
        $telephone = $token['id_token_payload']['phone_number'] ?? null;
        if ($telephone) {
            $trustedAccounts = $this->trustedAccountsLocator->getList($telephone);
            if ($trustedAccounts->getTotalCount() > 0) {
                /** @var VippsCustomerInterface $vippsCustomer */
                $vippsCustomer = current($trustedAccounts->getItems());
                return $this->customerRegistry->retrieve($vippsCustomer->getCustomerEntityId());
            }
        }

        return null;
    }
}
