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

namespace Vipps\Login\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\ResourceModel\Grid\CollectionFactory as GridCollectionFactory;
use Magento\Customer\Model\ResourceModel\Grid\Collection as GridCollection;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AccountsProvider
 * @package Vipps\Login\Model\Customer
 */
class AccountsProvider
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GridCollection
     */
    private $gridCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * AccountsProvider constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GridCollectionFactory $gridCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GridCollectionFactory $gridCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->gridCollectionFactory = $gridCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param string|null $phone
     * @param string|null $email
     *
     * @return CustomerInterface[]|null
     * @throws LocalizedException
     */
    public function get($phone, $email = null)
    {
        $emails = $this->findEmailsByPhone($phone);
        if ($email) {
            $emails[] = $email;
        }

        if ($emails) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('email', array_unique($emails), 'in')->create();
            $result = $this->customerRepository->getList($searchCriteria);
            return $result->getItems();
        }
        return null;
    }

    /**
     * @param string $phone
     *
     * @return array
     */
    private function findEmailsByPhone($phone)
    {
        if (!$phone) {
            return [];
        }

        /** @var GridCollection $collection */
        $collection = $this->gridCollectionFactory->create();

        $phones[] = $phone;
        $phones[] = preg_replace('/[^\d]/', '', $phone);

        foreach (array_unique($phones) as $phone) {
            $this->filterGroupBuilder->addFilter($this->filterBuilder->setField('billing_telephone')
                ->setValue($phone)
                ->setConditionType('eq')
                ->create());
        }

        $this->searchCriteriaBuilder->setFilterGroups([$this->filterGroupBuilder->create()]);
        $this->collectionProcessor->process($this->searchCriteriaBuilder->create(), $collection);

        $result = [];
        foreach ($collection->getItems() as $item) {
            /** @var $item CustomerInterface */
            $result[] = $item->getEmail();
        }
        return $result;
    }
}
