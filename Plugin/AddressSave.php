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

namespace Vipps\Login\Plugin;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;

/**
 * @codeCoverageIgnore
 */
class AddressSave
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
     * AddressSave constructor.
     *
     * @param RequestInterface $request
     * @param SessionManagerInterface $customerSession
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param VippsAddressManagementInterface $vippsAddressManagement
     */
    public function __construct(
        RequestInterface $request,
        SessionManagerInterface $customerSession,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        VippsAddressManagementInterface $vippsAddressManagement
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->vippsAddressManagement = $vippsAddressManagement;
    }

    /**
     * @param AddressRepositoryInterface $subject
     * @param AddressInterface $address
     *
     * @return AddressInterface
     */
    public function afterSave(AddressRepositoryInterface $subject, AddressInterface $address)
    {
        $addressId = $this->request->getParam('vipps_address_id');
        if ($addressId) {
            $customerModel = $this->customerSession->getCustomer();
            try {
                $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customerModel->getDataModel());
                $vippsAddress = $this->vippsCustomerAddressRepository->getById($addressId);
                if ($vippsAddress->getVippsCustomerId() != $vippsCustomer->getEntityId()) {
                    return $address;
                }
                $this->vippsAddressManagement->link($vippsAddress, $address);
            } catch (NoSuchEntityException $e) {

            }
        }

        return $address;
    }
}
