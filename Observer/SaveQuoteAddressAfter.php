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

namespace Vipps\Login\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Address;
use Vipps\Login\Model\ResourceModel\VippsQuoteAddressesRelation as ResourceModel;
use Vipps\Login\Model\VippsQuoteAddressesRelationFactory;
use Psr\Log\LoggerInterface;


class SaveQuoteAddressAfter implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var VippsQuoteAddressesRelationFactory
     */
    private $vippsQuoteAddressesFactory;

    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * ShippingAddressManagement constructor.
     *
     * @param VippsQuoteAddressesRelationFactory $vippsQuoteAddressesFactory
     * @param ResourceModel $resourceModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        VippsQuoteAddressesRelationFactory $vippsQuoteAddressesFactory,
        ResourceModel $resourceModel,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->vippsQuoteAddressesFactory = $vippsQuoteAddressesFactory;
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var Address $quoteAddress */
        $quoteAddress = $observer->getEvent()->getData('quote_address');
        $extAttributes = $quoteAddress->getExtensionAttributes();
        if (!empty($extAttributes)) {
            try {
                $vippsAddressId = $extAttributes->getVippsAddressId();
                if ($vippsAddressId) {
                    //todo check if address is available for customer
                    $vippsQuoteAddress = $this->vippsQuoteAddressesFactory->create();
                    $this->resourceModel->load(
                        $vippsQuoteAddress,
                        $vippsAddressId,
                        'vipps_customer_address_id'
                    );

                    if (!$vippsQuoteAddress->getId()) {
                        $vippsQuoteAddress->setQuoteAddressId($quoteAddress->getId());
                        $vippsQuoteAddress->setVippsCustomerAddressId($vippsAddressId);
                        $this->resourceModel->save($vippsQuoteAddress);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}

