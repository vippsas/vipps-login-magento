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

namespace Vipps\Login\Plugin\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AccountManagementInterface as Subject;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Model\TokenProviderInterface;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Api\VippsAccountManagementInterface;

/**
 * Class AccountManagement
 * @package Vipps\Login\Plugin\Customer\Api
 */
class AccountManagement
{
    /**
     * @var TokenProviderInterface
     */
    private $accessTokenProvider;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * @var VippsAccountManagementInterface
     */
    private $vippsAccountManagement;

    /**
     * @var VippsAddressManagementInterface
     */
    private $vippsAddressManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AccountManager constructor.
     *
     * @param TokenProviderInterface $accessTokenProvider
     * @param UserInfoCommand $userInfoCommand
     * @param VippsAccountManagementInterface $vippsAccountManagement
     * @param VippsAddressManagementInterface $vippsAddressManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        TokenProviderInterface $accessTokenProvider,
        UserInfoCommand $userInfoCommand,
        VippsAccountManagementInterface $vippsAccountManagement,
        VippsAddressManagementInterface $vippsAddressManagement,
        LoggerInterface $logger
    ) {
        $this->accessTokenProvider = $accessTokenProvider;
        $this->userInfoCommand = $userInfoCommand;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->vippsAddressManagement = $vippsAddressManagement;
        $this->logger = $logger;
    }

    /**
     * @param Subject $subject
     * @param CustomerInterface $result
     *
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateAccount(Subject $subject, CustomerInterface $result)
    {
        try {
            $accessToken = $this->accessTokenProvider->get();
            if ($accessToken) {
                $userInfo = $this->userInfoCommand->execute($accessToken);
                $vippsCustomer = $this->vippsAccountManagement->link($userInfo, $result);
                $this->vippsAddressManagement->apply($userInfo, $vippsCustomer, $result);
            }
        } catch (\Throwable $t) {
            $this->logger->critical($t);
        }

        return $result;
    }
}
