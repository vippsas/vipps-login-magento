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

namespace Vipps\Login\Plugin\Quote;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Quote\Model\ResourceModel\Quote\Address\Collection;
use Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CartSave
 * @package Vipps\Login\Plugin
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartSave
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SessionManagerInterface|Session
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
     * @var VippsAddressManagementInterface
     */
    private $vippsAddressManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * CartSave constructor.
     *
     * @param RequestInterface $request
     * @param SessionManagerInterface $customerSession
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param VippsAddressManagementInterface $vippsAddressManagement
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        SessionManagerInterface $customerSession,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        AddressRepositoryInterface $addressRepository,
        VippsAddressManagementInterface $vippsAddressManagement,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->vippsAddressManagement = $vippsAddressManagement;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     *
     * @return CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(CartRepositoryInterface $subject, $result, CartInterface $quote)
    {
        $customerModel = $this->customerSession->getCustomer();
        try {
            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customerModel->getDataModel());
            $vippsRelatedAddresses = $this->retrieveVippsRelatedAddresses($quote->getId());
            foreach ($vippsRelatedAddresses as $vippsRelatedAddress) {
                $customerAddress = $this->addressRepository->getById($vippsRelatedAddress->getCustomerAddressId());
                $vippsAddress = $this->vippsCustomerAddressRepository->getById(
                    $vippsRelatedAddress->getVippsCustomerAddressId()
                );
                if ($vippsAddress->getVippsCustomerId() != $vippsCustomer->getEntityId() ||
                    !$this->vippsAddressManagement->areTheSame($vippsAddress, $vippsCustomer, $customerAddress)
                ) {
                    continue;
                }
                $this->vippsAddressManagement->link($vippsAddress, $customerAddress);
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->debug($e);
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }

        return $quote;
    }

    /**
     * @param int $quoteId
     *
     * @return Collection
     */
    private function retrieveVippsRelatedAddresses($quoteId)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setQuoteFilter($quoteId);
        $collection->getSelect()
            ->reset('columns')
            ->columns(['customer_address_id'])
            ->joinInner(
                ['vqar' => $collection->getTable('vipps_quote_addresses_relation')],
                'vqar.quote_address_id = address_id',
                ['vipps_customer_address_id']
            )
            ->where('customer_address_id IS NOT NULL')
            ->where('vipps_customer_address_id IS NOT NULL');

        return $collection;
    }
}
