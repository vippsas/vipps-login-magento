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

namespace Vipps\Login\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\DataObjectHelper;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
use Vipps\Login\Api\Data\VippsCustomerAddressInterfaceFactory;
use Vipps\Login\Model\ResourceModel\VippsCustomerAddress as VippsCustomerAddressResource;

/**
 * Class VippsCustomerAddress
 * @package Vipps\Login\Model
 */
class VippsCustomerAddress extends AbstractModel
{
    /**
     * @var VippsCustomerAddressInterfaceFactory
     */
    private $vippsCustomerAddressFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * VippsCustomer constructor.
     *
     * @param VippsCustomerAddressInterfaceFactory $vippsCustomerAddressFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        VippsCustomerAddressInterfaceFactory $vippsCustomerAddressFactory,
        DataObjectHelper $dataObjectHelper,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->vippsCustomerAddressFactory = $vippsCustomerAddressFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Init resource model and id field
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(VippsCustomerAddressResource::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * @return VippsCustomerAddressInterface
     */
    public function getDataModel()
    {
        $data = $this->getData();

        $dataObject = $this->vippsCustomerAddressFactory->create();
        $this->dataObjectHelper->populateWithArray($dataObject, $data, VippsCustomerAddressInterface::class);

        return $dataObject;
    }
}
