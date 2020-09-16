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

namespace Vipps\Login\Block\Account;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Directory\Model\CountryFactory;
use Magento\Customer\Model\Session;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;

/**
 * Class AddressList
 * @package Vipps\Login\Block\Account
 */
class AddressList extends Template
{
    /**
     * @var VippsAccountManagementInterface
     */
    private $vippsAccountManagement;

    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var VippsCustomerAddressRepositoryInterface
     */
    private $vippsCustomerAddressRepository;

    /**
     * AddressList constructor.
     *
     * @param VippsAccountManagementInterface $vippsAccountManagement
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param SessionManagerInterface $customerSession
     * @param Template\Context $context
     * @param CountryFactory $countryFactory
     * @param array $data
     */
    public function __construct(
        VippsAccountManagementInterface $vippsAccountManagement,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        SessionManagerInterface $customerSession,
        Template\Context $context,
        CountryFactory $countryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->customerSession = $customerSession;
        $this->countryFactory = $countryFactory;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
    }

    /**
     * @return array|VippsCustomerAddressInterface[]
     */
    public function getVippsAddresses()
    {
        $customerModel = $this->customerSession->getCustomer();
        $customer = $customerModel->getDataModel();
        if ($this->vippsAccountManagement->isLinked($customer)) {
            try {
                $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer);
            } catch (NoSuchEntityException $e) {
                $this->_logger->debug($e->getMessage());
                return [];
            }

            $vippsAddressResult = $this->vippsCustomerAddressRepository->getByVippsCustomer($vippsCustomer);
            $vippsAddresses = $vippsAddressResult->getItems();
            return $vippsAddresses;
        }

        return [];
    }

    /**
     * Check if address is new or applying vipps address.
     *
     * @return bool
     */
    public function isNewAddress()
    {
        $isVippsApply = $this->getRequest()->getParam('vipps_address_id');
        $isNewAddress = empty($this->getRequest()->getParam('id'));

        return $isNewAddress && !$isVippsApply;
    }

    /**
     * @return int|null
     */
    public function getVippsAddressId()
    {
        return $this->getRequest()->getParam('vipps_address_id');
    }

    /**
     * @param $countryId
     *
     * @return string
     */
    public function getCountryName($countryId)
    {
        return $this->countryFactory->create()->loadByCode(
            $countryId
        )->getName();
    }

    /**
     * @return string
     */
    public function renderPhone()
    {
        $customerModel = $this->customerSession->getCustomer();
        $phone = '';
        try {
            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customerModel->getDataModel());
            $phone = $vippsCustomer->getTelephone();
        } catch (NoSuchEntityException $e) {
            $this->_logger->debug($e->getMessage());
        }

        return $phone;
    }
}
