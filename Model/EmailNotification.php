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

namespace Vipps\Login\Model;

use Vipps\Login\Api\Data\VippsCustomerInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\MailException;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class EmailNotification
 * @package Vipps\Login\Model
 */
class EmailNotification
{
    /**
     * @var string
     */
    const CUSTOMER_CONFIRM_URL = 'vipps/login/emailConfirm/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var CustomerViewHelper
     */
    private $customerViewHelper;

    /**
     * EmailNotification constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param DataObjectProcessor $dataObjectProcessor
     * @param ScopeResolverInterface $scopeResolver
     * @param TransportBuilder $transportBuilder
     * @param DataObjectFactory $dataObjectFactory
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DataObjectProcessor $dataObjectProcessor,
        ScopeResolverInterface $scopeResolver,
        TransportBuilder $transportBuilder,
        DataObjectFactory $dataObjectFactory,
        CustomerViewHelper $customerViewHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->scopeResolver = $scopeResolver;
        $this->transportBuilder = $transportBuilder;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerViewHelper = $customerViewHelper;
    }

    /**
     * @param VippsCustomerInterface $vippsCustomer
     * @param CustomerInterface $customer
     *
     * @throws MailException
     */
    public function resendConfirmation(VippsCustomerInterface $vippsCustomer, CustomerInterface $customer)
    {
        $store = $this->scopeResolver->getScope();

        $customerEmailData = $this->dataObjectProcessor
            ->buildOutputDataArray($customer, CustomerInterface::class);
        $vippsCustomerEmailData = $this->dataObjectProcessor
            ->buildOutputDataArray($vippsCustomer, VippsCustomerInterface::class);

        $customerEmailData['name'] = $this->customerViewHelper->getCustomerName($customer);

        $templateParams = [
            'customer' => $this->dataObjectFactory->create(['data' => $customerEmailData]),
            'vippsCustomer' => $this->dataObjectFactory->create(['data' => $vippsCustomerEmailData]),
            'back_url' => '',
            'store' => $store,
            'url' => self::CUSTOMER_CONFIRM_URL
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('vipps_login_confirmation')
            ->setTemplateOptions(['area' => 'frontend', 'store' => $store->getId()])
            ->setTemplateVars($templateParams)
            ->setFrom('support', $store)
            ->addTo($customer->getEmail(), $customer->getFirstname() . ' ' . $customer->getLastname())
            ->getTransport();

        $transport->sendMessage();
    }
}
