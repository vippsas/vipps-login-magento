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

namespace Vipps\Login\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManagerInterface;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;

/**
 * Customer section
 */
class VippsCustomer implements SectionSourceInterface
{
    /**
     * @var Session|SessionManagerInterface
     */
    private $customerSession;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var VippsCustomerAddressRepositoryInterface
     */
    private $vippsCustomerAddressRepository;

    /**
     * VippsCustomer constructor.
     *
     * @param SessionManagerInterface $customerSession
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     */
    public function __construct(
        SessionManagerInterface $customerSession,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
    ) {
        $this->customerSession = $customerSession;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $result = [];
        if (!$this->customerSession->isLoggedIn()) {
            return $result;
        }

        $customer = $this->customerSession->getCustomer();

        $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer->getDataModel());
        if (!$vippsCustomer->getEntityId()) {
            return $result;
        }

        $addressResult = $this->vippsCustomerAddressRepository->getByVippsCustomer($vippsCustomer);
        foreach ($addressResult->getItems() as $vippsCustomerAddress) {
            $result['addresses'][] = [
                'country_id' => $vippsCustomerAddress->getCountry(),
                'postalcode' => $vippsCustomerAddress->getPostalCode(),
                'city' => $vippsCustomerAddress->getRegion(),
                'telephone' => $vippsCustomer->getTelephone(),
                'street' => $vippsCustomerAddress->getStreetAddress(),
                'id' => $vippsCustomerAddress->getEntityId()
            ];
            if ($vippsCustomerAddress->getWasChanged() &&
                $vippsCustomer->getSyncAddressMode() !== VippsCustomerInterface::NEVER_UPDATE
            ) {
                $result['addressUpdated'] = true;
            }
        }

        return $result;
    }
}
