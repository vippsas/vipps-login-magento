<?php
/**
 * Copyright 2018 Vipps
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 *  documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 *  the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 *  and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 *  TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 *  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 *  CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 *  IN THE SOFTWARE.
 */
namespace Vipps\Login\Plugin\Customer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Vipps\Login\Api\Data\VippsCustomerInterfaceFactory;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;

/**
 * Class CustomerPlugin
 * @package Vipps\Login\Plugin\Model\Customer
 */
class CustomerPlugin
{
    /**
     * @var VippsCustomerInterfaceFactory
     */
    private $vippsCustomerFactory;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * CustomerPlugin constructor.
     *
     * @param VippsCustomerInterfaceFactory $vippsCustomerFactory
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     */
    public function __construct(
        VippsCustomerInterfaceFactory $vippsCustomerFactory,
        VippsCustomerRepositoryInterface $vippsCustomerRepository
    ) {
        $this->vippsCustomerFactory = $vippsCustomerFactory;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
    }

    /**
     * @param CustomerRepository $subject
     * @param CustomerInterface $result
     * @param CustomerInterface $customer
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function afterSave(CustomerRepository $subject, CustomerInterface $result, CustomerInterface $customer)
    {
        /** @var CustomerExtensionInterface $extensionAttributes */
        $extensionAttributes = $customer->getExtensionAttributes();
        if ($extensionAttributes) {
            $vippsCustomer = $this->vippsCustomerFactory->create();
            $vippsCustomer->setCustomerEntityId($result->getId());
            $vippsCustomer->setEmail($result->getEmail());
            $vippsCustomer->setTelephone($extensionAttributes->getVippsTelephone());
            $vippsCustomer->setLinked(true);

            $this->vippsCustomerRepository->save($vippsCustomer);
        }
    }
}
