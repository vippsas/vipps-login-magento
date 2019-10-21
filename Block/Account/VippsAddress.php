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

namespace Vipps\Login\Block\Account;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
use Vipps\Login\Api\VippsAccountManagementInterface;

/**
 * Class VippsAddress
 * @package Vipps\Login\Block\Account
 */
class VippsAddress extends Template
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
     * Link constructor.
     *
     * @param VippsAccountManagementInterface $vippsAccountManagement
     * @param SessionManagerInterface $customerSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        VippsAccountManagementInterface $vippsAccountManagement,
        SessionManagerInterface $customerSession,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->customerSession = $customerSession;
    }

    /**
     * @param bool $onlyNotLinked
     *
     * @return array|\Vipps\Login\Api\Data\VippsCustomerAddressInterface[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function getVippsAddresses($onlyNotLinked = false)
    {
        $customerModel = $this->customerSession->getCustomer();
        $customer = $customerModel->getDataModel();
        if ($this->vippsAccountManagement->isLinked($customer)){
            $addresses = $this->vippsAccountManagement->getAddresses($customer);
            if ($onlyNotLinked) {
                $addresses = array_filter($addresses, function ($item){
                    /** @var $item VippsCustomerAddressInterface  */
                    return $item->getCustomerAddressId() ? false : true;
                });
            }
            return $addresses;
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
}
