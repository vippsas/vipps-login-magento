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

namespace Vipps\Login\Controller\Login\Redirect\Action;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Vipps\Login\Model\Customer\TrustedAccountsLocator;
use Magento\Customer\Model\CustomerRegistry;

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
     * Login constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param SessionManagerInterface $sessionManager
     * @param TrustedAccountsLocator $trustedAccountsLocator
     * @param CustomerRegistry $customerRegistry
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        SessionManagerInterface $sessionManager,
        TrustedAccountsLocator $trustedAccountsLocator,
        CustomerRegistry $customerRegistry
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->sessionManager = $sessionManager;
        $this->trustedAccountsLocator = $trustedAccountsLocator;
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * @param array $token
     *
     * @return bool|Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute($token)
    {
        $customer = $this->getCustomerForLogin($token);
        if ($customer) {
            $this->sessionManager->setCustomerAsLoggedIn($customer);

            $redirect = $this->redirectFactory->create();
            $redirect->setPath('customer/account');
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
                $customerData = $trustedAccounts->getItems()[0];
                return $this->customerRegistry->retrieveByEmail($customerData->getEmail());
            }
        }
        return null;
    }
}
