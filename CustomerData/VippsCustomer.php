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

namespace Vipps\Login\CustomerData;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

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
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * VippsCustomer constructor.
     *
     * @param SessionManagerInterface $customerSession
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        SessionManagerInterface $customerSession,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $result = [];
        $customer = $this->customerSession->getCustomer();
        if (!$this->customerSession->isLoggedIn()) {
            return $result;
        }

        try {
            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer->getDataModel());
        } catch (NoSuchEntityException $e) {
            $this->logger->debug($e->getMessage());
            return $result;
        }

        if (!$vippsCustomer->getLinked()) {
            return $result;
        }

        $addressesResult = $this->vippsCustomerAddressRepository->getByVippsCustomer($vippsCustomer);
        foreach ($addressesResult->getItems() as $address) {
            $result['addresses'][] = [
                'country_id' => $address->getCountry(),
                'postcode' => $address->getPostalCode(),
                'city' => $address->getRegion(),
                'telephone' => $vippsCustomer->getTelephone(),
                'street' => $address->getStreetAddress(),
                'id' => $address->getEntityId()
            ];
            if ($address->getWasChanged() &&
                $address->getCustomerAddressId() &&
                $vippsCustomer->getSyncAddressMode() === VippsCustomerInterface::MANUAL_UPDATE
            ) {
                $magentoAddress = $this->addressRepository->getById($address->getCustomerAddressId());
                $customerDisplayName = $magentoAddress->getFirstname() . ' ' . $magentoAddress->getLastname();

                $result['show_popup'] = !(bool)$this->customerSession->getDisableVippsAddressUpdatePrompt();
                $result['newAddress'] = [
                    'country_id' => $address->getCountry(),
                    'postalcode' => $address->getPostalCode(),
                    'city' => $address->getRegion(),
                    'telephone' => $vippsCustomer->getTelephone(),
                    'street' => $address->getStreetAddress(),
                    'customer_display_name' => $customerDisplayName
                ];
                $result['oldAddress'] = [
                    'country_id' => $magentoAddress->getCountryId(),
                    'postalcode' => $magentoAddress->getPostcode(),
                    'city' => $magentoAddress->getCity(),
                    'telephone' => $magentoAddress->getTelephone(),
                    'street' => $this->formatAddress($magentoAddress->getStreet()),
                    'customer_display_name' => $customerDisplayName
                ];
            }
        }

        return $result;
    }

    /**
     * @param $magentoAddress
     *
     * @return string
     */
    private function formatAddress($magentoAddress)
    {
        if (is_array($magentoAddress)) {
            $magentoAddress = implode(PHP_EOL, $magentoAddress);
        }

        return $magentoAddress;
    }
}
