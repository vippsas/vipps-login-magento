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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\VippsAddressManagementInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;

/**
 * Class PasswordConfirm
 * @package Vipps\Login\Controller\Login
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressUpdate extends AccountBase
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var VippsAddressManagementInterface
     */
    private $vippsAddressManagement;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var VippsCustomerAddressRepositoryInterface
     */
    private $vippsCustomerAddressRepository;

    /**
     * AddressUpdate constructor.
     *
     * @param Context $context
     * @param SessionManagerInterface $customerSession
     * @param SerializerInterface $serializer
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $resultRawFactory
     * @param VippsAddressManagementInterface $vippsAddressManagement
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $customerSession,
        SerializerInterface $serializer,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        VippsAddressManagementInterface $vippsAddressManagement,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
    ) {
        parent::__construct($context, $customerSession);
        $this->serializer = $serializer;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->vippsAddressManagement = $vippsAddressManagement;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $this->customerSession->setDisableVippsAddressUpdatePrompt(true);
            $syncData = $this->serializer->unserialize($this->getRequest()->getContent());
        } catch (\Exception $e) {
            return $resultRaw;
        }

        if (!$this->isValid($syncData)) {
            return $resultRaw;
        }

        $response = [
            'errors' => false,
            'message' => __('Updated successfully')
        ];

        try {
            $customerModel = $this->customerSession->getCustomer();
            $customer = $customerModel->getDataModel();
            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer);
            if (!$vippsCustomer->getEntityId()) {
                throw new LocalizedException(__('You are not linked to a Vipps profile.'));
            }

            if ($syncData['sync_address_remember']) {
                $vippsCustomer->setSyncAddressMode($syncData['sync_address_mode']);
                $this->vippsCustomerRepository->save($vippsCustomer);
            }

            $vippsAddressesResult = $this->vippsCustomerAddressRepository
                ->getByVippsCustomer($vippsCustomer);

            foreach ($vippsAddressesResult->getItems() as $item) {
                if ($item->getWasChanged() &&
                    $syncData['sync_address_mode'] !== VippsCustomerInterface::NEVER_UPDATE
                ) {
                    $this->vippsAddressManagement->convert(
                        $customer,
                        $vippsCustomer,
                        $item,
                        false,
                        true
                    );
                }
            }
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Throwable $e) {
            $response = [
                'errors' => true,
                'message' => __('An error occurred.')
            ];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * @param $mode
     *
     * @return bool
     */
    private function isValid($mode)
    {
        if (!$mode ||
            !array_key_exists('sync_address_mode', $mode) ||
            $this->getRequest()->getMethod() !== 'POST' ||
            !$this->getRequest()->isXmlHttpRequest() ||
            !in_array($mode['sync_address_mode'], [
                VippsCustomerInterface::NEVER_UPDATE,
                VippsCustomerInterface::MANUAL_UPDATE,
                VippsCustomerInterface::AUTO_UPDATE
            ])
        ) {
            return false;
        }

        return true;
    }
}
