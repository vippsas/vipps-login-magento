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

namespace Vipps\Login\Model\Customer;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Grid\CollectionFactory as GridCollectionFactory;
use Magento\Customer\Model\ResourceModel\Grid\Collection as GridCollection;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

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
     * @var Share
     */
    private $configShare;

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
     * @param Share $configShare
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GridCollectionFactory $gridCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        CustomerRepositoryInterface $customerRepository,
        Share $configShare
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->gridCollectionFactory = $gridCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerRepository = $customerRepository;
        $this->configShare = $configShare;
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
            $this->searchCriteriaBuilder->addFilter('email', array_unique($emails), 'in');
            if ($this->configShare->isWebsiteScope()) {
                $this->searchCriteriaBuilder->addFilter(
                    'website_id',
                    $this->storeManager->getWebsite()->getId(),
                    'eq'
                );
            }

            $result = $this->customerRepository->getList($this->searchCriteriaBuilder->create());
            return $result->getItems();
        }

        return null;
    }

    /**
     * @param $phone
     *
     * @return array
     * @throws LocalizedException
     */
    private function findEmailsByPhone($phone)
    {
        if (!$phone) {
            return [];
        }

        /** @var GridCollection $collection */
        $collection = $this->gridCollectionFactory->create();

        $this->searchCriteriaBuilder->addFilter(
            'billing_telephone',
            $this->preparePhonePattern($phone),
            'like'
        );

        if ($this->configShare->isWebsiteScope()) {
            $this->searchCriteriaBuilder->addFilter(
                'website_id',
                $this->storeManager->getWebsite()->getId(),
                'eq'
            );
        }

        $this->collectionProcessor->process($this->searchCriteriaBuilder->create(), $collection);

        $result = [];
        foreach ($collection->getItems() as $item) {
            /** @var $item CustomerInterface */
            $result[] = $item->getEmail();
        }

        return $result;
    }

    /**
     * @param $phone
     *
     * @return string
     */
    private function preparePhonePattern($phone)
    {
        $phone = preg_replace('/[^\d]/', '', $phone);

        $length = strlen($phone);
        //remove norwegian country code
        if ($length > 8 && strpos($phone, '47') === 0) {
            $phone = substr($phone, 2, $length - 2);
        }

        $tmpPhone = '%';
        $length = strlen($phone);
        for ($i = 0; $i < $length; $i++) {
            $tmpPhone .= $phone[$i] . '%';
        }

        return $tmpPhone;
    }
}
