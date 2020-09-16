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

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
use Vipps\Login\Api\Data\VippsCustomerAddressSearchResultsInterface;
use Vipps\Login\Api\Data\VippsCustomerAddressSearchResultsInterfaceFactory;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Model\ResourceModel\VippsCustomerAddress\Collection;
use Vipps\Login\Model\ResourceModel\VippsCustomerAddress\CollectionFactory;
use Vipps\Login\Model\VippsCustomerAddressFactory as ModelFactory;

/**
 * Class VippsCustomerAddressRepository
 * @package Vipps\Login\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VippsCustomerAddressRepository implements VippsCustomerAddressRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var VippsCustomerAddressSearchResultsInterfaceFactory
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
     * @var VippsCustomerAddress
     */
    private $resourceModel;

    /**
     * @var ModelFactory
     */
    private $modelFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * VippsCustomerAddressRepository constructor.
     *
     * @param CollectionProcessorInterface $collectionProcessor
     * @param VippsCustomerAddressSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param VippsCustomerAddress $resourceModel
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModelFactory $modelFactory
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        VippsCustomerAddressSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        VippsCustomerAddress $resourceModel,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModelFactory $modelFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param $id
     *
     * @return VippsCustomerAddressInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        /** @var \Vipps\Login\Model\VippsCustomerAddress $customer */
        $vippsAddressModel = $this->modelFactory->create();
        $this->resourceModel->load($vippsAddressModel, $id, 'entity_id');
        if (!$vippsAddressModel->getId()) {
            // customer does not exist
            throw NoSuchEntityException::singleField('id', $id);
        }

        $vippsAddress = $vippsAddressModel->getDataModel();

        return $vippsAddress;
    }

    /**
     * @param VippsCustomerAddressInterface $vippsCustomerAddress
     *
     * @return mixed|VippsCustomerAddressInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Vipps\Login\Api\Data\VippsCustomerAddressInterface $vippsCustomerAddress)
    {
        $modelData = $this->extensibleDataObjectConverter->toNestedArray(
            $vippsCustomerAddress,
            [],
            VippsCustomerAddressInterface::class
        );

        /** @var \Vipps\Login\Model\VippsCustomerAddress $vippsCustomerAddressModel */
        $vippsCustomerAddressModel = $this->modelFactory->create(['data' => $modelData]);
        $this->resourceModel->save($vippsCustomerAddressModel);

        $vippsCustomerAddress = $vippsCustomerAddressModel->getDataModel();

        return $vippsCustomerAddress;
    }

    /**
     * @param VippsCustomerInterface $vippsCustomer
     *
     * @return VippsCustomerAddressSearchResultsInterface
     */
    public function getByVippsCustomer(VippsCustomerInterface $vippsCustomer)
    {
        $this->searchCriteriaBuilder->addFilter('vipps_customer_id', $vippsCustomer->getEntityId());
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResult = $this->getList($searchCriteria);

        return $searchResult;
    }

    /**
     * @param VippsCustomerInterface $vippsCustomer
     *
     * @return array|VippsCustomerAddressInterface[]
     */
    public function getNotLinkedAddresses(\Vipps\Login\Api\Data\VippsCustomerInterface $vippsCustomer)
    {
        $result = $this->getByVippsCustomer($vippsCustomer);
        $addresses = array_filter($result->getItems(), function ($item) {
            /** @var $item VippsCustomerAddressInterface  */
            return $item->getCustomerAddressId() ? false : true;
        });
        return $addresses;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return VippsCustomerAddressSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var VippsCustomerAddressSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setTotalCount($collection->getSize());

        $records = [];
        /** @var \Vipps\Login\Model\VippsCustomerAddress $model */
        foreach ($collection as $model) {
            $records[] = $model->getDataModel();
        }
        $searchResults->setItems($records);

        return $searchResults;
    }

    /**
     * @param VippsCustomerAddressInterface $vippsCustomerAddress
     *
     * @return $this|bool
     * @throws \Exception
     */
    public function delete(\Vipps\Login\Api\Data\VippsCustomerAddressInterface $vippsCustomerAddress)
    {
        $modelData = $this->extensibleDataObjectConverter->toNestedArray(
            $vippsCustomerAddress,
            [],
            VippsCustomerAddressInterface::class
        );

        /** @var \Vipps\Login\Model\VippsCustomerAddress $vippsCustomerAddressModel */
        $vippsCustomerAddressModel = $this->modelFactory->create(['data' => $modelData]);
        $this->resourceModel->delete($vippsCustomerAddressModel);

        return true;
    }

    /**
     * @param VippsCustomerInterface $vippsCustomer
     *
     * @return $this|bool
     * @throws \Exception
     */
    public function deleteByVippsCustomer(\Vipps\Login\Api\Data\VippsCustomerInterface $vippsCustomer)
    {
        $vippsAddressResult = $this->getByVippsCustomer($vippsCustomer);

        foreach ($vippsAddressResult->getItems() as $item) {
            $this->delete($item);
        }

        return true;
    }
}
