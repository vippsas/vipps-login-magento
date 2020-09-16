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

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface VippsCustomerAddressRepositoryInterface
 * @package Vipps\Login\Api
 */
interface VippsCustomerAddressRepositoryInterface
{
    /**
     * @param $id
     *
     * @throws NoSuchEntityException
     * @return \Vipps\Login\Api\Data\VippsCustomerAddressInterface
     */
    public function getById($id);

    /**
     * Create or update a vipps customer Address record.
     *
     * @param \Vipps\Login\Api\Data\VippsCustomerAddressInterface $vippsCustomerAddress
     * @return $this
     */
    public function save(\Vipps\Login\Api\Data\VippsCustomerAddressInterface $vippsCustomerAddress);

    /**
     * @param \Vipps\Login\Api\Data\VippsCustomerInterface $customer
     *
     * @return \Vipps\Login\Api\Data\VippsCustomerAddressSearchResultsInterface
     */
    public function getByVippsCustomer(\Vipps\Login\Api\Data\VippsCustomerInterface $customer);

    /**
     * @param Data\VippsCustomerInterface $customer
     *
     * @return array|\Vipps\Login\Api\Data\VippsCustomerAddressInterface[]
     */
    public function getNotLinkedAddresses(\Vipps\Login\Api\Data\VippsCustomerInterface $customer);

    /**
     * Retrieve vipps customer records which match a specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Vipps\Login\Api\Data\VippsCustomerAddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param Data\VippsCustomerAddressInterface $vippsCustomerAddress
     *
     * @return bool true on success
     */
    public function delete(\Vipps\Login\Api\Data\VippsCustomerAddressInterface $vippsCustomerAddress);

    /**
     * @param Data\VippsCustomerInterface $vippsCustomer
     *
     * @return bool true on success
     */
    public function deleteByVippsCustomer(\Vipps\Login\Api\Data\VippsCustomerInterface $vippsCustomer);
}
