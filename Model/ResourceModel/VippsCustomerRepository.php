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

namespace Vipps\Login\Model\ResourceModel;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\Data\VippsCustomerSearchResultsInterfaceFactory;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Vipps\Login\Model\ResourceModel\VippsCustomer\CollectionFactory;
use Vipps\Login\Model\ResourceModel\VippsCustomer\Collection;
use Vipps\Login\Model\VippsCustomerFactory as ModelFactory;
use Vipps\Login\Model\ResourceModel\VippsCustomer as VippsCustomerResource;

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
     * @var VippsCustomerResource
     */
    private $vippsCustomerResource;

    /**
     * VippsCustomerRepository constructor.
     *
     * @param CollectionProcessorInterface $collectionProcessor
     * @param VippsCustomerSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param ModelFactory $modelFactory
     * @param VippsCustomerResource $vippsCustomerResource
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        VippsCustomerSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        ModelFactory $modelFactory,
        VippsCustomerResource $vippsCustomerResource
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->modelFactory = $modelFactory;
        $this->vippsCustomerResource = $vippsCustomerResource;
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
        $vippsCustomerModel = $this->modelFactory->create(['data' => $modelData]);
        $this->vippsCustomerResource->save($vippsCustomerModel);

        $vippsCustomer = $vippsCustomerModel->getDataModel();

        return $vippsCustomer;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Vipps\Login\Api\Data\VippsCustomerSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Vipps\Login\Api\Data\VippsCustomerSearchResultsInterface $searchResults */
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
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        if (isset($this->instances['ids'][$id])) {
            return $this->instances['ids'][$id];
        }

        /** @var \Vipps\Login\Model\VippsCustomer $vippsCustomer */
        $vippsCustomerModel = $this->modelFactory->create();
        $this->vippsCustomerResource->load($vippsCustomerModel, $id, 'entity_id');
        if (!$vippsCustomerModel->getEntityId()) {
            throw NoSuchEntityException::singleField('entity_id', $id);
        }

        $vippsCustomer = $vippsCustomerModel->getDataModel();

        return $vippsCustomer;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return VippsCustomerInterface
     * @throws NoSuchEntityException
     */
    public function getByCustomer(CustomerInterface $customer)
    {
        $customerId = $customer->getId();
        if (isset($this->instances['customer_entity_ids'][$customerId])) {
            return $this->instances['customer_entity_ids'][$customerId];
        }

        /** @var \Vipps\Login\Model\VippsCustomer $vippsCustomer */
        $vippsCustomerModel = $this->modelFactory->create();
        $this->vippsCustomerResource->load($vippsCustomerModel, $customerId, 'customer_entity_id');
        if (!$vippsCustomerModel->getEntityId()) {
            throw NoSuchEntityException::singleField('customer_entity_id', $customerId);
        }

        $vippsCustomer = $vippsCustomerModel->getDataModel();

        return $vippsCustomer;
    }
}
