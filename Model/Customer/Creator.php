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
namespace Vipps\Login\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Store\Model\StoreManagerInterface;
use Vipps\Login\Api\Data\UserInfoInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * Class Creator
 * @package Vipps\Login\Model\Customer
 */
class Creator
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * Creator constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        ExtensionAttributesFactory $extensionAttributesFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * @param UserInfoInterface $userInfo
     *
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function create(UserInfoInterface $userInfo)
    {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($this->storeManager->getWebsite()->getWebsiteId());

        $customer->setEmail($userInfo->getEmail());
        $customer->setFirstname($userInfo->getName());
        $customer->setLastname($userInfo->getFamilyName());

        $extensionAttributes = $customer->getExtensionAttributes() ??
            $this->extensionAttributesFactory->create(CustomerInterface::class);

        $extensionAttributes->setVippsTelephone($userInfo->getPhoneNumber());
        $extensionAttributes->setVippsLinked(true);

        $customer->setExtensionAttributes($extensionAttributes);

        $this->customerRepository->save($customer);
    }
}
