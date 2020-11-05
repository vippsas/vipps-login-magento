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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
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
     * @param SessionManagerInterface $customerSession
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $resultRawFactory
     * @param VippsAddressManagementInterface $vippsAddressManagement
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        SessionManagerInterface $customerSession,
        RequestInterface $request,
        SerializerInterface $serializer,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        VippsAddressManagementInterface $vippsAddressManagement,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($customerSession, $request, $logger);
        $this->serializer = $serializer;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->vippsAddressManagement = $vippsAddressManagement;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
    }

    /**
     * @return ResponseInterface|Json|Raw|ResultInterface
     */
    public function execute()
    {
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        if (!$this->canProcess()) {
            return $resultRaw;
        }

        try {
            $this->customerSession->setDisableVippsAddressUpdatePrompt(true);
            $syncData = $this->serializer->unserialize($this->getRequest()->getContent());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
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
            $this->logger->critical($e);
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Throwable $e) {
            $this->logger->critical($e);
            $response = [
                'errors' => true,
                'message' => __('An error occurred.')
            ];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);

        return $resultJson;
    }

    /**
     * @param $mode
     *
     * @return bool
     */
    private function isValid($mode): bool
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
