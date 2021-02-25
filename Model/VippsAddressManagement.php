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

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @param FormFactory $formFactory
     * @param Random $mathRand
     */
    public function __construct(
        VippsCustomerAddressInterfaceFactory $vippsCustomerAddressFactory,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        FormFactory $formFactory,
        Random $mathRand
    ) {
        $this->vippsCustomerAddressFactory = $vippsCustomerAddressFactory;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->mathRand = $mathRand;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
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
            $this->assign($vippsAddress, $vippsCustomer, $addressesResult->getItems());
            $this->convert($customer, $vippsCustomer, $vippsAddress, $hasDefault, false);
        }
    }

    /**
     * @param UserInfoInterface $userInfo
     * @param VippsCustomerInterface $vippsCustomer
     *
     * @return array|VippsCustomerAddressInterface[]
     */
    public function fetchAddresses(
        UserInfoInterface $userInfo,
        VippsCustomerInterface $vippsCustomer
    ) {
        $vippsAddressResult = $this->vippsCustomerAddressRepository->getByVippsCustomer($vippsCustomer);
        $vippsAddresses = $vippsAddressResult->getItems();

        $newVippsAddresses = $userInfo->getAddress();
        $result = [];

        foreach ($vippsAddresses as $item) {
            $match = false;
            foreach ($newVippsAddresses as $addressType => $address) {
                if ($address['address_type'] != $item->getAddressType()) {
                    continue;
                }
                $match = true;
                if ($this->isVippsAddressChanged($item, $address)) {
                    $item = $this->populateWithArray($item, $address);
                    $item->setWasChanged(true);
                    $result[] = $this->vippsCustomerAddressRepository->save($item);
                }
                unset($newVippsAddresses[$addressType]);
                break;
            }
            if (!$match) {
                $this->vippsCustomerAddressRepository->delete($item);
            }
        }

        foreach ($newVippsAddresses as $address) {
            /** @var VippsCustomerAddressInterface $vippsCustomerAddress */
            $vippsCustomerAddress = $this->vippsCustomerAddressFactory->create();
            $vippsCustomerAddress = $this->populateWithArray($vippsCustomerAddress, $address);
            $vippsCustomerAddress->setVippsCustomerId($vippsCustomer->getEntityId());
            if (isset($address['is_default']) && $address['is_default'] === true) {
                $vippsCustomerAddress->setIsDefault(true);
            }
            $result[] = $this->vippsCustomerAddressRepository->save($vippsCustomerAddress);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
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
        bool $hasDefault,
        bool $forceConvert
    ) {
        if (!$this->isConvertAllowed($vippsCustomer, $vippsAddress, $forceConvert)) {
            return false;
        }

        /** @var AddressInterface $magentoAddress */
        try {
            $magentoAddress = $this->addressRepository->getById($vippsAddress->getCustomerAddressId());
        } catch (NoSuchEntityException $e) {
            $magentoAddress = $this->addressDataFactory->create();
        }

        $magentoAddress->setCustomerId($customer->getId());
        $magentoAddress->setCity($vippsAddress->getRegion());
        $magentoAddress->setCountryId($vippsAddress->getCountry());
        $magentoAddress->setFirstname($customer->getFirstname());
        $magentoAddress->setLastname($customer->getLastname());
        $magentoAddress->setPostcode($vippsAddress->getPostalCode());

        $street = explode(PHP_EOL, $vippsAddress->getStreetAddress());

        $magentoAddress->setStreet($street);
        $magentoAddress->setTelephone($vippsCustomer->getTelephone());

        if (!$hasDefault &&
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

        $this->link($vippsAddress, $magentoAddress);

        return $magentoAddress;
    }

    /**
     * {@inheritdoc}
     *
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param VippsCustomerInterface $vippsCustomer
     * @param array $magentoAddresses
     *
     * @return bool
     */
    public function assign(
        VippsCustomerAddressInterface $vippsAddress,
        VippsCustomerInterface $vippsCustomer,
        array $magentoAddresses
    ) {
        if ($vippsAddress->getCustomerAddressId()) {
            return true;
        }

        foreach ($magentoAddresses as $address) {
            if ($this->areTheSame($vippsAddress, $vippsCustomer, $address)) {
                $this->link($vippsAddress, $address);
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param AddressInterface $magentoAddress
     */
    public function link(VippsCustomerAddressInterface $vippsAddress, AddressInterface $magentoAddress)
    {
        $vippsAddress->setWasChanged(false);
        $vippsAddress->setCustomerAddressId($magentoAddress->getId());
        $this->vippsCustomerAddressRepository->save($vippsAddress);
    }

    /**
     * Method compares magento address and vipps address.
     *
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param VippsCustomerInterface $vippsCustomer
     * @param AddressInterface $magentoAddress
     *
     * @return bool
     */
    public function areTheSame(
        VippsCustomerAddressInterface $vippsAddress,
        VippsCustomerInterface $vippsCustomer,
        AddressInterface $magentoAddress
    ) {
        $street = $magentoAddress->getStreet();
        if (is_array($street)) {
            $street = implode(PHP_EOL, $street);
        }

        /*
         * remove whitespaces
         */
        $street = preg_replace('/\W/', '', $street);
        $vippsStreet = preg_replace('/\W/', '', $vippsAddress->getStreetAddress());
        if (strcasecmp($vippsStreet, $street) !== 0) {
            return false;
        }

        /*
         * compare only digits
         */
        $postCode = preg_replace('/\D/', '', $magentoAddress->getPostcode());
        if (strcmp($vippsAddress->getPostalCode(), $postCode) !== 0) {
            return false;
        }

        /*
         * compare only digits
         */
        $phone = preg_replace('/\D/', '', $magentoAddress->getTelephone());
        if (strcmp($vippsCustomer->getTelephone(), $phone) !== 0) {
            return false;
        }

        /*
         * remove whitespaces
         */
        $city = preg_replace('/\W/', '', $magentoAddress->getCity());
        $vippsRegion = preg_replace('/\W/', '', $vippsAddress->getRegion());
        if (strcasecmp($vippsRegion, $city) !== 0) {
            return false;
        }

        if (strcasecmp($vippsAddress->getCountry(), $magentoAddress->getCountryId()) !== 0) {
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
        $vippsAddress->setStreetAddress(htmlspecialchars($address['street_address']));
        $vippsAddress->setAddressType($address['address_type']);
        $vippsAddress->setFormatted(htmlspecialchars($address['formatted']));
        $vippsAddress->setPostalCode($address['postal_code']);
        $vippsAddress->setRegion(htmlspecialchars($address['region']));

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
        if (strcasecmp($vippsAddress->getCountry(), $addressArr['country']) !== 0) {
            return true;
        }

        if (strcasecmp($vippsAddress->getStreetAddress(), $addressArr['street_address']) !== 0) {
            return true;
        }

        if (strcasecmp($vippsAddress->getPostalCode(), $addressArr['postal_code']) !== 0) {
            return true;
        }

        if (strcasecmp($vippsAddress->getRegion(), $addressArr['region']) !== 0) {
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
            if ($address->isDefaultShipping() || $address->isDefaultBilling()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param VippsCustomerInterface $vippsCustomer
     * @param VippsCustomerAddressInterface $vippsAddress
     * @param bool $forceConvert
     *
     * @return bool
     */
    private function isConvertAllowed(
        VippsCustomerInterface $vippsCustomer,
        VippsCustomerAddressInterface $vippsAddress,
        bool $forceConvert
    ) {
        if ($forceConvert) {
            return true;
        }

        if (!$vippsAddress->getCustomerAddressId()) {
            return true;
        }

        if (!$vippsAddress->getWasChanged()) {
            return false;
        }

        if ($vippsCustomer->getSyncAddressMode() == VippsCustomerInterface::AUTO_UPDATE) {
            return true;
        }

        return false;
    }
}
