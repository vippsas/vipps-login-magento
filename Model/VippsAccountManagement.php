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

use Psr\Log\LoggerInterface;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\Data\VippsCustomerInterfaceFactory;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Math\Random;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class VippsCustomer
 * @package Vipps\Login\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VippsAccountManagement implements VippsAccountManagementInterface
{
    /**
     * @var VippsCustomerInterfaceFactory
     */
    private $vippsCustomerFactory;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * @var EmailNotification
     */
    private $emailNotification;

    /**
     * @var Random
     */
    private $mathRand;

    /**
     * @var VippsCustomerAddressRepositoryInterface
     */
    private $vippsCustomerAddressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * VippsAccountManagement constructor.
     *
     * @param VippsCustomerInterfaceFactory $vippsCustomerFactory
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository
     * @param EmailNotification $emailNotification
     * @param Random $mathRand
     * @param LoggerInterface $logger
     */
    public function __construct(
        VippsCustomerInterfaceFactory $vippsCustomerFactory,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        VippsCustomerAddressRepositoryInterface $vippsCustomerAddressRepository,
        EmailNotification $emailNotification,
        Random $mathRand,
        LoggerInterface $logger
    ) {
        $this->vippsCustomerFactory = $vippsCustomerFactory;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->emailNotification = $emailNotification;
        $this->mathRand = $mathRand;
        $this->vippsCustomerAddressRepository = $vippsCustomerAddressRepository;
        $this->logger = $logger;
    }

    /**
     * @param UserInfoInterface $userInfo
     * @param CustomerInterface $customer
     *
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     */
    public function resendConfirmation(UserInfoInterface $userInfo, CustomerInterface $customer)
    {
        try {
            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer);
            if ($vippsCustomer->getLinked()) {
                throw new InvalidTransitionException(__('Account already confirmed'));
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->debug($e->getMessage());
        }

        $vippsCustomer = $this->getPair($userInfo, $customer);
        $vippsCustomer->setConfirmationKey($this->mathRand->getUniqueHash());
        $vippsCustomer->setConfirmationExp(time() + 3600);

        $this->vippsCustomerRepository->save($vippsCustomer);

        // send email
        $this->emailNotification->resendConfirmation($vippsCustomer, $customer);
    }

    /**
     * @param $id
     * @param $key
     *
     * @return VippsCustomerInterface|null
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function confirm($id, $key)
    {
        $vippsCustomer = $this->vippsCustomerRepository->getById($id);
        if ($key === $vippsCustomer->getConfirmationKey() && $vippsCustomer->getConfirmationExp() > time()) {
            $vippsCustomer->setLinked(true);
            $vippsCustomer->setConfirmationKey(null);
            $vippsCustomer->setConfirmationExp(null);
            return $this->vippsCustomerRepository->save($vippsCustomer);
        }

        return null;
    }

    /**
     * @param UserInfoInterface $userInfo
     * @param CustomerInterface $customer
     *
     * @return VippsCustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function link(UserInfoInterface $userInfo, CustomerInterface $customer)
    {
        $vippsCustomer = $this->getPair($userInfo, $customer);
        $vippsCustomer->setLinked(true);

        return $this->vippsCustomerRepository->save($vippsCustomer);
    }

    /**
     * Check if customer is already linked to vipps account.
     *
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    public function isLinked(CustomerInterface $customer)
    {
        try {
            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer);
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return (bool) $vippsCustomer->getLinked();
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return bool|VippsCustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function unlink(CustomerInterface $customer)
    {
        try {
            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer);
        } catch (NoSuchEntityException $e) {
            return true;
        }

        $this->vippsCustomerAddressRepository->deleteByVippsCustomer($vippsCustomer);
        $vippsCustomer->setLinked(false);

        return $this->vippsCustomerRepository->save($vippsCustomer);
    }

    /**
     * @param UserInfoInterface $userInfo
     * @param CustomerInterface $customer
     *
     * @return VippsCustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function getPair(UserInfoInterface $userInfo, CustomerInterface $customer)
    {
        try {
            $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer);
        } catch (NoSuchEntityException $e) {
            $vippsCustomer= $this->vippsCustomerFactory->create();
        }

        $vippsCustomer->setCustomerEntityId($customer->getId());
        $vippsCustomer->setWebsiteId($customer->getWebsiteId());
        $vippsCustomer->setEmail($customer->getEmail());
        $vippsCustomer->setTelephone($userInfo->getPhoneNumber());

        return $this->vippsCustomerRepository->save($vippsCustomer);
    }
}
