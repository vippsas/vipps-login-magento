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

namespace Vipps\Login\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Math\Random;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
use Vipps\Login\Api\Data\VippsCustomerAddressInterfaceFactory;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;

/**
 * Class VippsCustomer
 * @package Vipps\Login\Model
 */
class VippsAddressManagement implements VippsAddressManagementInterface
{
    /**
     * @var VippsCustomerAddressRepositoryInterface
     */
    private $vippsCustomerAddressRepository;

    /**
     * @var Random
     */
    private $mathRand;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var VippsCustomerAddressInterfaceFactory
     */
    private $vippsCustomerAddressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressDataFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionDataFactory;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * VippsAddressManagement constructor.
     *
     * @param VippsCustomerAddressInterfaceFactory $vippsCustomerAddressFactory
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param FormFactory $formFactory
     * @param Random $mathRand
     */
    public function __construct(
        VippsCustomerAddressInterfaceFactory $vippsCustomerAddressFactory,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        FormFactory $formFactory,
        Random $mathRand
    ) {
        $this->vippsCustomerAddressFactory = $vippsCustomerAddressFactory;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->mathRand = $mathRand;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->formFactory = $formFactory;
    }

    /**
     * @param UserInfoInterface $userInfo
     * @param VippsCustomerInterface $vippsCustomer
     * @param CustomerInterface $customer
     *
     * @throws LocalizedException
     */
    public function apply(
        UserInfoInterface $userInfo,
        VippsCustomerInterface $vippsCustomer,
        CustomerInterface $customer
    ) {
        $vippsAddresses = $this->fetchAddresses($userInfo, $vippsCustomer);

        $this->searchCriteriaBuilder->addFilter('parent_id', $vippsCustomer->getCustomerEntityId());
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $addressesResult = $this->addressRepository->getList($searchCriteria);
        $hasDefault = $this->hasDefault($addressesResult->getItems());

        foreach ($vippsAddresses as $vippsAddress) {
            $result = $this->merge($vippsAddress, $addressesResult->getItems());
            if (!$result) {
                $this->convert($customer, $vippsCustomer, $vippsAddress, $hasDefault);
            }
        }
    }

    /**
     * @param UserInfoInterface $userInfo
     * @param VippsCustomerInterface $vippsCustomer
     *
     * @return VippsCustomerAddressInterface[]|[]
     * @throws LocalizedException
     */
    public function fetchAddresses(
        UserInfoInterface $userInfo,
        VippsCustomerInterface $vippsCustomer
    ) {
        $this->searchCriteriaBuilder->addFilter('vipps_customer_id', $vippsCustomer->getEntityId());
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $vippsAddressResult = $this->vippsCustomerAddressRepository->getList($searchCriteria);

        $newVippsAddresses = $userInfo->getAddress();
        $result = [];
        if ($vippsAddressResult->getTotalCount()) {
            foreach ($vippsAddressResult->getItems() as $item) {
                foreach ($newVippsAddresses as $address) {
                    if ($address['address_type'] == $item->getAddressType()) {
                        $item = $this->populateWithArray($item, $address);
                        $result[] = $this->vippsCustomerAddressRepository->save($item);
                        break;
                    }
                }
            }
        } else {
            foreach ($newVippsAddresses as $address) {
                /** @var VippsCustomerAddressInterface $vippsCustomerAddress */
                $vippsCustomerAddress = $this->vippsCustomerAddressFactory->create();
                $vippsCustomerAddress = $this->populateWithArray($vippsCustomerAddress, $address);
                $vippsCustomerAddress->setVippsCustomerId($vippsCustomer->getEntityId());
                if ($vippsCustomerAddress->getAddressType() == VippsCustomerAddressInterface::ADDRESS_TYPE_HOME) {
                    $vippsCustomerAddress->setIsDefault(true);
                }
                $result[] = $this->vippsCustomerAddressRepository->save($vippsCustomerAddress);
            }
        }

        return $result;
    }

    /**
     * @param CustomerInterface $customer
     * @param VippsCustomerInterface $vippsCustomer
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param bool $hasDefault
     *
     * @return bool|AddressInterface
     * @throws LocalizedException
     */
    public function convert(
        CustomerInterface $customer,
        VippsCustomerInterface $vippsCustomer,
        VippsCustomerAddressInterface $vippsAddress,
        bool $hasDefault
    ) {
        if ($vippsAddress->getIsConverted() && $vippsAddress->getCustomerAddressId()) {
            return true;
        }

        /** @var AddressInterface $magentoAddress */
        $magentoAddress = $this->addressDataFactory->create();
        $magentoAddress->setCustomerId($customer->getId());
        $magentoAddress->setCity($vippsAddress->getRegion());//todo check value
        $magentoAddress->setCountryId($vippsAddress->getCountry());
        $magentoAddress->setFirstname($customer->getFirstname());
        $magentoAddress->setLastname($customer->getLastname());
        $magentoAddress->setPostcode($vippsAddress->getPostalCode());

        /** @var \Magento\Customer\Api\Data\RegionInterface $regionDataObject */
        $regionDataObject = $this->regionDataFactory->create();
        $regionDataObject->setRegion($vippsAddress->getRegion());
        $magentoAddress->setRegion($regionDataObject);

        $street = explode('\n', $vippsAddress->getStreetAddress());
        $magentoAddress->setStreet($street);

        $magentoAddress->setTelephone($vippsCustomer->getTelephone());

        if (
            !$hasDefault &&
            $vippsAddress->getAddressType() == VippsCustomerAddressInterface::ADDRESS_TYPE_HOME
        ) {
            $magentoAddress->setIsDefaultShipping(true);
            $magentoAddress->setIsDefaultBilling(true);
        }

        try {
            $magentoAddress = $this->addressRepository->save($magentoAddress);
        } catch (InputException $e) {
            return false;
        }


        $vippsAddress->setCustomerAddressId($magentoAddress->getId());
        $this->vippsCustomerAddressRepository->save($vippsAddress);

        return $magentoAddress;
    }

    /**
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param AddressInterface[] $magentoAddresses
     *
     * @return bool
     */
    public function merge(VippsCustomerAddressInterface $vippsAddress, array $magentoAddresses)
    {
        foreach ($magentoAddresses as $address) {
            if ($this->areTheSame($vippsAddress, $address)) {
                $this->link($vippsAddress, $address);
                return true;
            }
        }

        return false;
    }

    /**
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param AddressInterface $magentoAddress
     */
    public function link(VippsCustomerAddressInterface $vippsAddress, AddressInterface $magentoAddress)
    {
        $vippsAddress->setIsConverted(true);
        $vippsAddress->setCustomerAddressId($magentoAddress->getId());
        $this->vippsCustomerAddressRepository->save($vippsAddress);
    }

    /**
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param AddressInterface $magentoAddress
     *
     * @return bool
     */
    public function areTheSame(VippsCustomerAddressInterface $vippsAddress, AddressInterface $magentoAddress)
    {
        if (strtolower($vippsAddress->getCountry()) != strtolower($magentoAddress->getCountryId())) {
            return false;
        }

        if (strtolower($vippsAddress->getRegion()) != strtolower($magentoAddress->getRegion())) {
            return false;
        }

        if (
            filter_var($vippsAddress->getPostalCode(), FILTER_SANITIZE_NUMBER_INT) !=
            filter_var($magentoAddress->getPostcode(), FILTER_SANITIZE_NUMBER_INT)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param array $address
     *
     * @return VippsCustomerAddressInterface
     */
    public function populateWithArray(VippsCustomerAddressInterface $vippsAddress, array $address)
    {
        $vippsAddress->setCountry($address['country']);
        $vippsAddress->setStreetAddress($address['street_address']);
        $vippsAddress->setAddressType($address['address_type']);
        $vippsAddress->setFormatted($address['formatted']);
        $vippsAddress->setPostalCode($address['postal_code']);
        $vippsAddress->setRegion($address['region']);

        if ($this->isVippsAddressChanged($vippsAddress, $address)) {
            $vippsAddress->setWasChanged(true);
        }

        return $vippsAddress;
    }

    /**
     * Check if vipps Address was changed on vipps side.
     *
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param array $addressArr
     *
     * @return bool
     */
    private function isVippsAddressChanged(VippsCustomerAddressInterface $vippsAddress, array $addressArr)
    {
        if (strtolower($vippsAddress->getCountry()) != strtolower($addressArr['country'])) {
            return true;
        }

        if (strtolower($vippsAddress->getStreetAddress()) != strtolower($addressArr['street_address'])) {
            return true;
        }

        if (strtolower($vippsAddress->getPostalCode()) != strtolower($addressArr['postal_code'])) {
            return true;
        }

        if (strtolower($vippsAddress->getRegion()) != strtolower($addressArr['region'])) {
            return true;
        }

        return false;
    }

    /**
     * @param AddressInterface[] $magentoAddresses
     *
     * @return bool
     */
    private function hasDefault(array $magentoAddresses)
    {
        foreach ($magentoAddresses as $address) {
            if ($address->isDefaultShipping() or $address->isDefaultBilling()) {
                return true;
            }
        }

        return false;
    }
}
