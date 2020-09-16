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

namespace Vipps\Login\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Model\TokenPayloadProvider;
use Vipps\Login\Api\Data\UserInfoInterfaceFactory;

/**
 * Class CustomerRegistrationFormFill
 * @package Vipps\Login\Observer
 */
class CustomerRegistrationFormFill implements ObserverInterface
{
    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var TokenPayloadProvider
     */
    private $payloadProvider;

    /**
     * @var UserInfoInterfaceFactory
     */
    private $userInfoFactory;

    /**
     * CustomerRegistrationFormFill constructor.
     *
     * @param SessionManagerInterface $sessionManager
     * @param UserInfoInterfaceFactory $userInfoFactory
     * @param TokenPayloadProvider $payloadProvider
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        UserInfoInterfaceFactory $userInfoFactory,
        TokenPayloadProvider $payloadProvider
    ) {
        $this->sessionManager = $sessionManager;
        $this->payloadProvider = $payloadProvider;
        $this->userInfoFactory = $userInfoFactory;
    }

    /**
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if (!$this->sessionManager->getCustomerFormData()) {
            $payload = $this->payloadProvider->get();
            if ($payload) {
                $userInfo = $this->userInfoFactory->create(['data' => $payload]);
                $this->setCustomerFormData($userInfo);
            }
        }
    }

    /**
     * @param UserInfoInterface $userInfo
     */
    private function setCustomerFormData(UserInfoInterface $userInfo)
    {
        $customerFormData = [
            'email' => $userInfo->getEmail(),
            'firstname' => $userInfo->getGivenName(),
            'lastname' => $userInfo->getFamilyName(),
            'birthday' => $userInfo->getBirthdate(),
            'telephone' => $userInfo->getPhoneNumber()
        ];

        $address = $this->getAddressByType($userInfo, 'home');
        if ($address) {
            $customerFormData['postcode'] = $address['postal_code'];
            $customerFormData['country_id'] = $address['country'];
            $customerFormData['street'] = $address['street_address'];
            $customerFormData['city'] = $address['region'];
        }

        $this->sessionManager->setCustomerFormData($customerFormData);
    }

    /**
     * @param UserInfoInterface $userInfo
     * @param $type
     *
     * @return mixed|null
     */
    private function getAddressByType(UserInfoInterface $userInfo, $type)
    {
        $addresses = $userInfo->getAddress() ?? [];
        foreach ($addresses as $address) {
            if ($address['address_type'] == $type) {
                return $address;
            }
        }

        return null;
    }
}
