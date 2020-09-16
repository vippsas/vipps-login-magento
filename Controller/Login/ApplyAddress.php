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

namespace Vipps\Login\Controller\Login;

use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Vipps\Login\Model\VippsAccountManagement;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ApplyAddress
 * @package Vipps\Login\Controller\Login
 */
class ApplyAddress extends AccountBase
{
    /**
     * @var VippsAccountManagement
     */
    private $vippsAccountManagement;

    /**
     * @var VippsCustomerAddressRepositoryInterface
     */
    private $vippsCustomerAddressRepository;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * Unlink constructor.
     *
     * @param Context $context
     * @param SessionManagerInterface $customerSession
     * @param VippsAccountManagement $vippsAccountManagement
     * @param ManagerInterface $messageManager
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $customerSession,
        VippsAccountManagement $vippsAccountManagement,
        ManagerInterface $messageManager,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        VippsCustomerRepositoryInterface $vippsCustomerRepository
    ) {
        parent::__construct($context, $customerSession);
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->messageManager = $messageManager;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultRedirect = $this->resultRedirectFactory->create();

        $addressId = $this->getRequest()->getParam('id', false);
        $params = [];
        if ($addressId) {
            try {
                $customer = $this->customerSession->getCustomer();
                $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer->getDataModel());
                $vippsAddress = $this->vippsCustomerAddressRepository->getById($addressId);

                if (!$vippsAddress->getVippsCustomerId() == $vippsCustomer->getEntityId()) {
                    $this->messageManager->addErrorMessage(__('We can\'t delete the address right now.'));
                }
                $this->customerSession->setAddressFormData([
                    'telephone' => $vippsCustomer->getTelephone(),
                    'postcode' => $vippsAddress->getPostalCode(),
                    'city' => $vippsAddress->getRegion(),
                    'country_id' => $vippsAddress->getCountry(),
                    'street' => explode(PHP_EOL, $vippsAddress->getStreetAddress()),
                    'region' => $vippsAddress->getRegion()
                ]);

                $params = ['vipps_address_id' => $addressId];
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('We can\'t delete the address right now.'));
            }
        }

        $resultRedirect->setPath('customer/address/new', $params);

        return $resultRedirect;
    }
}
