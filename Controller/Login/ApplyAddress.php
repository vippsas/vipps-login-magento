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
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\VippsAccountManagement;

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
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;
    private ConfigInterface $config;

    /**
     * ApplyAddress constructor.
     *
     * @param RedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param SessionManagerInterface $customerSession
     * @param VippsAccountManagement $vippsAccountManagement
     * @param ManagerInterface $messageManager
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     */
    public function __construct(
        RedirectFactory                         $resultRedirectFactory,
        RequestInterface                        $request,
        LoggerInterface                         $logger,
        SessionManagerInterface                 $customerSession,
        VippsAccountManagement                  $vippsAccountManagement,
        ManagerInterface                        $messageManager,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        VippsCustomerRepositoryInterface        $vippsCustomerRepository,
        ConfigInterface      $config
    ) {
        parent::__construct($customerSession, $request, $logger);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->messageManager = $messageManager;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultForward */
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
                    'telephone'  => $vippsCustomer->getTelephone(),
                    'postcode'   => $vippsAddress->getPostalCode(),
                    'city'       => $vippsAddress->getRegion(),
                    'country_id' => $vippsAddress->getCountry(),
                    'street'     => explode(PHP_EOL,
                        $vippsAddress->getStreetAddress(),
                        $this->config->getCustomerStreetLinesNumber()
                    ),
                    'region'     => $vippsAddress->getRegion()
                ]);
                $params = ['vipps_address_id' => $addressId];
            } catch (NoSuchEntityException $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('We can\'t delete the address right now.'));
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('An error occurred. Please, try again later.'));
            }
        }

        $resultRedirect->setPath('customer/address/new', $params);

        return $resultRedirect;
    }
}
