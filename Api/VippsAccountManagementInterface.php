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

namespace Vipps\Login\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
use Vipps\Login\Api\Data\VippsCustomerInterface;

/**
 * Interface VippsAccountManagementInterface
 * @package Vipps\Login\Api
 */
interface VippsAccountManagementInterface
{
    /**
     * @param UserInfoInterface $userInfo
     * @param CustomerInterface $customer
     *
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     */
    public function resendConfirmation(UserInfoInterface $userInfo, CustomerInterface $customer);

    /**
     * @param $id
     * @param $key
     *
     * @return VippsCustomerInterface|null
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function confirm($id, $key);

    /**
     * @param UserInfoInterface $userInfo
     * @param CustomerInterface $customer
     *
     * @return VippsCustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function link(UserInfoInterface $userInfo, CustomerInterface $customer);

    /**
     * Check if customer is already linked to vipps account.
     *
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    public function isLinked(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     *
     * @return mixed
     */
    public function unlink(CustomerInterface $customer);

    /**
     * @param UserInfoInterface $userInfo
     * @param CustomerInterface $customer
     *
     * @return VippsCustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function getPair(UserInfoInterface $userInfo, CustomerInterface $customer);
}
