<?php
namespace Vipps\Login\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Math\Random;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\Data\VippsCustomerInterfaceFactory;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * Class VippsCustomer
 * @package Vipps\Login\Model
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
     * VippsAccountManagement constructor.
     *
     * @param VippsCustomerInterfaceFactory $vippsCustomerFactory
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param EmailNotification $emailNotification
     * @param Random $mathRand
     */
    public function __construct(
        VippsCustomerInterfaceFactory $vippsCustomerFactory,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        EmailNotification $emailNotification,
        Random $mathRand
    ) {
        $this->vippsCustomerFactory = $vippsCustomerFactory;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->emailNotification = $emailNotification;
        $this->mathRand = $mathRand;
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
        $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer);
        if ($vippsCustomer->getLinked()) {
            throw new InvalidTransitionException(__('Account already confirmed'));
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

    public function unlink()
    {
        // TODO: Implement unlink() method.
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
        $vippsCustomer = $this->vippsCustomerRepository->getByCustomer($customer);
        if (!$vippsCustomer->getEntityId()) {
            $vippsCustomer->setCustomerEntityId($customer->getId());
            //$vippsCustomer->setWebsiteId($customer->getWebsiteId());
            $vippsCustomer->setEmail($customer->getEmail());
            $vippsCustomer->setTelephone($userInfo->getPhoneNumber());
            return $this->vippsCustomerRepository->save($vippsCustomer);
        }
        return $vippsCustomer;
    }
}
