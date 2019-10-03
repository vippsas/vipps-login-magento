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

namespace Vipps\Login\Model\Customer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Vipps\Login\Api\Data\VippsCustomerSearchResultsInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Vipps\Login\Model\ResourceModel\VippsCustomerRepository;

/**
 * Class TrustedAccountsLocator
 * @package Vipps\Login\Model\Customer
 */
class TrustedAccountsLocator
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var VippsCustomerRepository
     */
    private $vippsCustomerRepository;

    /**
     * TrustedAccountsLocator constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param VippsCustomerRepository $customerRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        VippsCustomerRepositoryInterface $vippsCustomerRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
    }

    /**
     * @param string $phone
     *
     * @return VippsCustomerSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList($phone)
    {
        $this->searchCriteriaBuilder->addFilter('telephone', $phone);
        $this->searchCriteriaBuilder->addFilter('linked', true);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->vippsCustomerRepository->getList($searchCriteria);
    }
}
