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

namespace Vipps\Login\Controller\Login\Redirect\Action;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Validator\Exception as ValidatoException;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerRegistry;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\Customer\Creator;
use Vipps\Login\Model\RedirectUrlResolver;
use Psr\Log\LoggerInterface;

/**
 * Class Create
 * @package Vipps\Login\Controller\Login\Redirect\Action
 */
class Create implements ActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var SessionManagerInterface|Session
     */
    private $sessionManager;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * @var Creator
     */
    private $creator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RedirectUrlResolver
     */
    private $redirectUrlResolver;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Create constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param SessionManagerInterface $sessionManager
     * @param CustomerRegistry $customerRegistry
     * @param UserInfoCommand $userInfoCommand
     * @param RedirectUrlResolver $redirectUrlResolver
     * @param Creator $creator
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        SessionManagerInterface $sessionManager,
        CustomerRegistry $customerRegistry,
        UserInfoCommand $userInfoCommand,
        RedirectUrlResolver $redirectUrlResolver,
        Creator $creator,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->sessionManager = $sessionManager;
        $this->customerRegistry = $customerRegistry;
        $this->userInfoCommand = $userInfoCommand;
        $this->redirectUrlResolver = $redirectUrlResolver;
        $this->creator = $creator;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    /**
     * @param $token
     *
     * @return Redirect|mixed
     */
    public function execute($token)
    {
        $redirect = $this->redirectFactory->create();
        $userInfo = null;
        try {
            $userInfo = $this->userInfoCommand->execute($token['access_token']);
            $magentoCustomer = $this->creator->create($userInfo);
            $customer = $this->customerRegistry->retrieveByEmail($magentoCustomer->getEmail());
            $this->sessionManager->setCustomerAsLoggedIn($customer);
            $redirect->setUrl(
                $this->redirectUrlResolver->getRedirectUrl()
            );
        } catch (ValidatoException $e) {
            $this->messageManager->addErrorMessage($e);
            $this->setCustomerFormData($userInfo);
            $redirect = $this->redirectFactory->create();
            $redirect->setPath('customer/account/create');
        } catch(\Throwable $e) {
            $this->logger->critical($e);
            return false;
        }

        return $redirect;
    }

    /**
     * @param UserInfoInterface|null $userInfo
     */
    private function setCustomerFormData($userInfo)
    {
        if (!$userInfo instanceof UserInfoInterface) {
            return;
        }

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
