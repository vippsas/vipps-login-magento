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

namespace Vipps\Login\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Math\Random;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
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
     * @var VippsCustomerAddressFactory
     */
    private $vippsCustomerAddressFactory;

    /**
     * VippsAddressManagement constructor.
     *
     * @param VippsCustomerAddressFactory $vippsCustomerAddressFactory
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Random $mathRand
     */
    public function __construct(
        VippsCustomerAddressFactory $vippsCustomerAddressFactory,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Random $mathRand
    ) {
        $this->vippsCustomerAddressFactory = $vippsCustomerAddressFactory;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->mathRand = $mathRand;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param UserInfoInterface $userInfo
     * @param VippsCustomerInterface $vippsCustomer
     * @param bool $onlyDefault
     *
     * @return VippsCustomerAddressInterface[]|[]
     * @throws LocalizedException
     */
    public function fetchAddresses(
        UserInfoInterface $userInfo,
        VippsCustomerInterface $vippsCustomer,
        $onlyDefault = false
    ) {
        $this->searchCriteriaBuilder->addFilter('vipps_customer_id', $vippsCustomer->getEntityId());
        if ($onlyDefault) {
            $this->searchCriteriaBuilder->addFilter('is_default',true);
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $vippsAddressResult = $this->vippsCustomerAddressRepository->getList($searchCriteria);

        $newVippsAddresses = $userInfo->getAddress();
        $result = [];
        if ($vippsAddressResult->getTotalCount()) {
            foreach ($vippsAddressResult->getItems() as $item) {
                foreach ($newVippsAddresses as $address) {
                    if ($address['address_type'] == $item->getAddressType()) {
                        $item = $this->convertAddress($item, $address);
                        $result[] = $this->vippsCustomerAddressRepository->save($item);
                    }
                }
            }
        } else {
            foreach ($newVippsAddresses as $address) {
                /** @var VippsCustomerAddress $vippsCustomerAddress */
                $vippsCustomerAddress = $this->vippsCustomerAddressFactory->create();
                $vippsCustomerAddress = $this->convertAddress($vippsCustomerAddress, $address);
                $vippsCustomerAddress->setCustomerId($vippsCustomer->getEntityId());
                if ($vippsCustomerAddress->getAddressType() == VippsCustomerAddressInterface::ADDRESS_TYPE_HOME) {
                    $vippsCustomerAddress->setIsDefault(true);
                }
                $result[] = $this->vippsCustomerAddressRepository->save($vippsCustomerAddress);
            }
        }

        return $result;
    }

    public function merge()
    {
        // TODO: Implement merge() method.
    }

    /**
     * @param VippsCustomerAddressInterface $vippsCustomerAddress
     * @param array $address
     *
     * @return VippsCustomerAddressInterface
     */
    public function convertAddress(VippsCustomerAddressInterface $vippsCustomerAddress, array $address)
    {
        $vippsCustomerAddress->setCountry($address['country']);
        $vippsCustomerAddress->setStreetAddress($address['street_address']);
        $vippsCustomerAddress->setAddressType($address['address_type']);
        $vippsCustomerAddress->setFormatted($address['formatted']);
        $vippsCustomerAddress->setPostalCode($address['postal_code']);
        $vippsCustomerAddress->setRegion($address['region']);

        return $vippsCustomerAddress;
    }
}
