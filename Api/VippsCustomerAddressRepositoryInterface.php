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

namespace Vipps\Login\Api;

/**
 * Interface VippsCustomerAddressRepositoryInterface
 * @package Vipps\Login\Api
 */
interface VippsCustomerAddressRepositoryInterface
{
    /**
     * Create or update a vipps customer Address record.
     *
     * @param \Vipps\Login\Api\Data\VippsCustomerAddressInterface $vippsCustomerAddress
     * @return \Vipps\Login\Api\Data\VippsCustomerAddressInterface
     */
    public function save(\Vipps\Login\Api\Data\VippsCustomerAddressInterface $vippsCustomerAddress);

    /**
     * @param \Vipps\Login\Api\Data\VippsCustomerInterface $customer
     *
     * @return \Vipps\Login\Api\Data\VippsCustomerAddressSearchResultsInterface
     */
    public function getByVippsCustomer(\Vipps\Login\Api\Data\VippsCustomerInterface $customer);

    /**
     * Retrieve vipps customer records which match a specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Vipps\Login\Api\Data\VippsCustomerAddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
