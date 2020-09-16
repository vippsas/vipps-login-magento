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

namespace Vipps\Login\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Vipps\Login\Model\ConfigInterface;

/**
 * Class LayoutProcessor
 * @package Vipps\Login\Block\Checkout
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var VippsAccountManagementInterface
     */
    private $vippsAccountManagement;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var VippsCustomerAddressRepositoryInterface
     */
    private $vippsCustomerAddressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * LayoutProcessor constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param SessionManagerInterface $customerSession
     * @param VippsAccountManagementInterface $vippsAccountManagement
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SessionManagerInterface $customerSession,
        VippsAccountManagementInterface $vippsAccountManagement,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Process js Layout.
     *
     * @param mixed[] $jsLayout
     * @return mixed[]
     */
    public function process($jsLayout)
    {
        if (!$this->config->isEnabled()) {
            return $jsLayout;
        }

        if (!$this->customerSession->isLoggedIn()) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['customer-email']['children']
            ['before-login-form']['children']['vipps_login_form'] = $this->getVippsLoginButtonComponent();

            return $jsLayout;
        }

        $customerModel = $this->customerSession->getCustomer();
        $customer = $customerModel->getDataModel();
        try {
            if ($this->vippsAccountManagement->isLinked($customer)) {
                $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer);
                $addresses = $this->vippsCustomerAddressRepository->getNotLinkedAddresses($vippsCustomer);
                if (!$addresses) {
                    return $jsLayout;
                }

                $jsLayout = $this->processShippingAddress($jsLayout);
                $jsLayout = $this->processBillingAddress($jsLayout);

                $addressOptions[] = ['value' => '', 'label' => __('Select address')];
                foreach ($addresses as $address) {
                    $addressOptions[] = [
                        'value' => $address->getEntityId(),
                        'label' => $address->getFormatted(),
                        'address' => [
                            'country_id' => $address->getCountry(),
                            'postcode' => $address->getPostalCode(),
                            'city' => $address->getRegion(),
                            'telephone' => $vippsCustomer->getTelephone(),
                            'street' => $address->getStreetAddress()
                        ]
                    ];
                }
                $jsLayout['components']['checkoutProvider']['dictionaries']['vipps_addresses_list'] = $addressOptions;
            }
        } catch (\Throwable $t) {
            $this->logger->error($t);
        }

        return $jsLayout;
    }

    /**
     * @param $jsLayout
     *
     * @return mixed
     */
    private function processShippingAddress($jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
            ['vipps_address_box'] = $this->getVippsAddressComponent(
                'shippingAddress.custom_attributes.vipps_address_box'
            );
        }

        return $jsLayout;
    }

    /**
     * @param $jsLayout
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function processBillingAddress($jsLayout)
    {
        $paymentMethods = $jsLayout['components']['checkout']['children']['steps']['children']
                        ['billing-step']['children']['payment']['children']
                        ['payments-list']['children'] ?? [];
        foreach ($paymentMethods as $paymentCode => $value) {
            $paymentCode = str_replace('-form', '', $paymentCode);

            if (!isset($jsLayout['components']['checkout']['children']['steps']
                       ['children']['billing-step']['children']['payment']['children']
                       ['payments-list']['children'][$paymentCode . '-form'])) {
                continue;
            }
            $scope = 'billingAddress' . $paymentCode . '.custom_attributes.vipps_address_box';

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'][$paymentCode . '-form']['children']
            ['form-fields']['children']['vipps_address_box'] = $this->getVippsAddressComponent($scope);

        }

        return $jsLayout;
    }

    /**
     * Method returns UI component if vipps_address.
     *
     * @param $scope string
     *
     * @return array
     */
    private function getVippsAddressComponent($scope)
    {
        return [
            'component' => 'Vipps_Login/js/model/vipps-addresses',
            'config' => [
                'customScope' => $scope,
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
            ],
            'dataScope' => $scope,
            'label' => __('Use address from Vipps'),
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'validation' => [
                'required-entry' => false
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'deps' => ['checkoutProvider'],
            'imports' => [
                'setOptions' => "index = checkoutProvider:dictionaries.vipps_addresses_list"
            ]
        ];
    }

    /**
     * @return array
     */
    private function getVippsLoginButtonComponent()
    {
        return [
            'component' => 'Vipps_Login/js/view/checkout/vipps_checkout_login',
            'config' => [
                'template' => 'Vipps_Login/vipps-checkout-login/login-btn',
                'title' => __('Vipps login form'),
            ],
        ];
    }
}
