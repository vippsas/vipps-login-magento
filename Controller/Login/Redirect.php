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
use Vipps\Login\Gateway\Command\TokenCommand;
use Vipps\Login\Model\Customer\TrustedAccountsLocator;

/**
 * Class Redirect
 * @package Vipps\Login\Controller\Login
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
     * @var TrustedAccountsLocator
     */
    private $trustedAccountsLocator;

    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param CustomerRegistry $customerRegistry
     * @param SessionManagerInterface $sessionManager
     * @param TokenCommand $tokenCommand
     * @param TrustedAccountsLocator $trustedAccountsLocator
     */
    public function __construct(
        Context $context,
        CustomerRegistry $customerRegistry,
        SessionManagerInterface $sessionManager,
        TokenCommand $tokenCommand,
        TrustedAccountsLocator $trustedAccountsLocator
    ) {
        parent::__construct($context);
        $this->customerRegistry = $customerRegistry;
        $this->sessionManager = $sessionManager;
        $this->tokenCommand = $tokenCommand;
        $this->trustedAccountsLocator = $trustedAccountsLocator;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $result = $this->tokenCommand->execute();

        try {
            $list = $this->trustedAccountsLocator->getList($result->phone_number);
            if ($list->getTotalCount() > 0) {
                $customerData = $list->getItems()[0];
                $customer = $this->customerRegistry->retrieveByEmail($customerData->getEmail());
                $this->sessionManager->setCustomerAsLoggedIn($customer);

                return $this->_redirect('/');
            } else {
                return $this->_redirect('/');
            }
        } catch (\Throwable $t) {
            return 'An error occurred!' . $t->getMessage();
        }
    }
}
