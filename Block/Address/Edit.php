<?php
/**
 * Copyright 2018 Vipps
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
 * IN THE SOFTWARE
 */

declare(strict_types=1);

namespace Vipps\Login\Block\Address;

use Magento\Customer\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\AttributeChecker;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Block\Address\Edit as AddressEdit;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;

/**
 * Class Edit
 * @package Vipps\Login\Block\Address
 */
class Edit extends AddressEdit
{
    /**
     * @var VippsCustomerAddressRepositoryInterface
     */
    private $vippsCustomerAddressRepository;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionDataFactory;

    /**
     * @var VippsCustomerAddressInterface|null
     */
    private $vippsAddress = null;

    /**
     * Edit constructor.
     *
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param RegionInterfaceFactory $regionDataFactory
     * @param Context $context
     * @param Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CollectionFactory $countryCollectionFactory
     * @param Session $customerSession
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CurrentCustomer $currentCustomer
     * @param DataObjectHelper $dataObjectHelper
     * @param array $data
     * @param AttributeChecker|null $attributeChecker
     */
    public function __construct(
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        RegionInterfaceFactory $regionDataFactory,
        Context $context,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        RegionCollectionFactory $regionCollectionFactory,
        CollectionFactory $countryCollectionFactory,
        Session $customerSession,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        CurrentCustomer $currentCustomer,
        DataObjectHelper $dataObjectHelper,
        array $data = [],
        AttributeChecker $attributeChecker = null
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerSession,
            $addressRepository,
            $addressDataFactory,
            $currentCustomer,
            $dataObjectHelper,
            $data,
            $attributeChecker
        );
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->regionDataFactory = $regionDataFactory;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($addressId = $this->getRequest()->getParam('vipps_address_id')) {

            try {
                $customerModel = $this->_customerSession->getCustomer();
                $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customerModel->getDataModel());
                $this->vippsAddress = $this->vippsCustomerAddressRepository->getById($addressId);

                if ($this->vippsAddress->getVippsCustomerId() != $vippsCustomer->getEntityId()) {
                    return $this;
                }

                $this->_address->setTelephone($vippsCustomer->getTelephone());
                $this->_address->setPostcode($this->vippsAddress->getPostalCode());
                $this->_address->setCity($this->vippsAddress->getRegion());

                $street = explode(PHP_EOL, $this->vippsAddress->getStreetAddress());
                $this->_address->setStreet($street);

                /** @var \Magento\Customer\Api\Data\RegionInterface $regionDataObject */
                $regionDataObject = $this->regionDataFactory->create();
                $regionDataObject->setRegion($this->vippsAddress->getRegion());
                $this->_address->setRegion($regionDataObject);
                $this->_address->setCountryId($this->vippsAddress->getCountry());
            } catch (NoSuchEntityException $e) {
                //todo logger
            } catch (\Throwable $e) {

            }
        }

        return $this;
    }


    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'customer/address/formPost',
            [
                '_secure' => true,
                'id' => $this->getAddress()->getId(),
                'vipps_address_id' => $this->vippsAddress ? $this->vippsAddress->getEntityId() : null
            ]
        );
    }
}
