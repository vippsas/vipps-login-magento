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

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
use Vipps\Login\Api\Data\VippsCustomerInterface;

/**
 * Interface VippsAddressManagementInterface
 * @package Vipps\Login\Api
 * @api
 */
interface VippsAddressManagementInterface
{
    /**
     * Fetch addresses information from vipps API
     * update existing and save to DB.
     *
     * @param UserInfoInterface $userInfo
     * @param VippsCustomerInterface $vippsCustomer
     *
     * @return VippsCustomerAddressInterface[]|[]
     */
    public function fetchAddresses(UserInfoInterface $userInfo, VippsCustomerInterface $vippsCustomer);

    /**
     * Method applies vipps addresses information to magento addresses.
     *
     * @param UserInfoInterface $userInfo
     * @param VippsCustomerInterface $vippsCustomer
     * @param CustomerInterface $customer
     *
     * @return mixed
     */
    public function apply(
        UserInfoInterface $userInfo,
        VippsCustomerInterface $vippsCustomer,
        CustomerInterface $customer
    );

    /**
     * Method links vipps address with magento address.
     *
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param AddressInterface $magentoAddress
     *
     * @return void
     */
    public function link(VippsCustomerAddressInterface $vippsAddress, AddressInterface $magentoAddress);

    /**
     * Methods tries to assign vipps address to magento address.
     *
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param VippsCustomerInterface $vippsCustomer
     * @param AddressInterface[] $magentoAddresses
     *
     * @return bool
     */
    public function assign(
        VippsCustomerAddressInterface $vippsAddress,
        VippsCustomerInterface $vippsCustomer,
        array $magentoAddresses
    );

    /**
     * @param CustomerInterface $customer
     * @param VippsCustomerInterface $vippsCustomer
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param bool $hasDefault
     * @param bool $forceConvert
     *
     * @return mixed
     */
    public function convert(
        CustomerInterface $customer,
        VippsCustomerInterface $vippsCustomer,
        VippsCustomerAddressInterface $vippsAddress,
        bool $hasDefault,
        bool $forceConvert
    );

    /**
     * Method to compare Vipps Address and Magento address.
     *
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param VippsCustomerInterface $vippsCustomer
     * @param AddressInterface $magentoAddress
     *
     * @return bool
     */
    public function areTheSame(
        VippsCustomerAddressInterface $vippsAddress,
        VippsCustomerInterface $vippsCustomer,
        AddressInterface $magentoAddress
    );
}
