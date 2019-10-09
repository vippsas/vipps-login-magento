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

namespace Vipps\Login\Model\ResourceModel;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\Data\VippsCustomerSearchResultsInterfaceFactory;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Vipps\Login\Model\ResourceModel\VippsCustomer\CollectionFactory;
use Vipps\Login\Model\VippsCustomerFactory as ModelFactory;

/**
 * Class VippsCustomerRepository
 * @package Vipps\Login\Model\ResourceModel
 */
class VippsCustomerRepository implements VippsCustomerRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var VippsCustomerSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @var ModelFactory
     */
    private $modelFactory;

    /**
     * VippsCustomerRepository constructor.
     *
     * @param CollectionProcessorInterface $collectionProcessor
     * @param VippsCustomerSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param ModelFactory $modelFactory
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        VippsCustomerSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        ModelFactory $modelFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->modelFactory = $modelFactory;
    }

    /**
     * @param VippsCustomerInterface $customer
     *
     * @return VippsCustomerInterface
     * @throws \Exception
     */
    public function save(\Vipps\Login\Api\Data\VippsCustomerInterface $customer)
    {
        $modelData = $this->extensibleDataObjectConverter->toNestedArray(
            $customer,
            [],
            VippsCustomerInterface::class
        );

        /** @var \Vipps\Login\Model\VippsCustomer $vippsCustomer */
        $vippsCustomer = $this->modelFactory->create(['data' => $modelData]);
        $vippsCustomer->save();
        return $vippsCustomer->getDataModel();
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Vipps\Login\Api\Data\VippsCustomerSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setTotalCount($collection->getSize());

        $records = [];
        /** @var \Vipps\Login\Model\VippsCustomer $model */
        foreach ($collection as $model) {
            $records[] = $model->getDataModel();
        }
        $searchResults->setItems($records);
        return $searchResults;
    }

    /**
     * @param int $id
     *
     * @return VippsCustomerInterface
     */
    public function getById($id)
    {
        /** @var \Vipps\Login\Model\VippsCustomer $vippsCustomer */
        $vippsCustomer = $this->modelFactory->create();
        $vippsCustomer->load($id);

        return $vippsCustomer->getDataModel();
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return VippsCustomerInterface
     */
    public function getByCustomer(CustomerInterface $customer)
    {
        /** @var \Vipps\Login\Model\VippsCustomer $vippsCustomer */
        $vippsCustomer = $this->modelFactory->create();
        $vippsCustomer->load($customer->getId(), 'customer_entity_id');

        return $vippsCustomer->getDataModel();
    }
}
