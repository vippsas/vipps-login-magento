<?php
/**
 * Copyright 2021 Vipps
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
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Customer\Model\CustomerRegistry;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\Customer\AccountsProvider;

/**
 * Class Confirm
 * @package Vipps\Login\Controller\Login\Redirect\Action
 */
class Confirm implements ActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var AccountsProvider
     */
    private $accountsProvider;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * Login constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param CustomerRegistry $customerRegistry
     * @param AccountsProvider $accountsProvider
     * @param UserInfoCommand $userInfoCommand
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        CustomerRegistry $customerRegistry,
        AccountsProvider $accountsProvider,
        UserInfoCommand $userInfoCommand
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->customerRegistry = $customerRegistry;
        $this->accountsProvider = $accountsProvider;
        $this->userInfoCommand = $userInfoCommand;
    }

    /**
     * @param $token
     *
     * @return bool|Redirect
     * @throws LocalizedException
     */
    public function execute($token)
    {
        $userInfo = $this->userInfoCommand->execute($token['access_token']);

        $email = null;
        if ($userInfo->getIsEmailVerified()) {
            $email = $userInfo->getEmail();
        }

        $customers = $this->accountsProvider->get($userInfo->getPhoneNumber(), $email);

        if ($customers) {
            $redirect = $this->redirectFactory->create();
            $redirect->setPath('vipps/login/confirmation');
            return $redirect;
        }

        return false;
    }
}
