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
namespace Vipps\Login\Block\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Template;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;

/**
 * Class VippsConfiguration
 * @package Vipps\Login\Block\Account
 */
class VippsConfiguration extends Template
{
    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var VippsAccountManagementInterface
     */
    private $vippsAccountManagement;

    /**
     * VippsConfiguration constructor.
     *
     * @param Template\Context $context
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsAccountManagementInterface $vippsAccountManagement
     * @param SessionManagerInterface $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsAccountManagementInterface $vippsAccountManagement,
        SessionManagerInterface $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->vippsAccountManagement = $vippsAccountManagement;
    }

    /**
     * @return bool
     */
    public function isLinked()
    {
        $customer = $this->customerSession->getCustomer();
        return $this->vippsAccountManagement->isLinked($customer->getDataModel());
    }

    /**
     * @return int
     */
    public function getSyncAddressMode()
    {
        $customer = $this->customerSession->getCustomer();
        try {
            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer->getDataModel());
        } catch (NoSuchEntityException $e) {
            $this->_logger->debug($e->getMessage());
            return false;
        }

        return $vippsCustomer->getSyncAddressMode();
    }

    /**
     * Return the save action Url.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl('vipps/login/configurationSave');
    }
}
