<?php
namespace Vipps\Login\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\Data\VippsCustomerInterfaceFactory;
use Vipps\Login\Api\VippsAccountManagementInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;

/**
 * Class VippsCustomer
 * @package Vipps\Login\Model
 */
class VippsAccountManagement implements VippsAccountManagementInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var VippsCustomerInterfaceFactory
     */
    private $vippsCustomerFactory;

    /**
     * @var VippsCustomerRepositoryInterface
     */
    private $vippsCustomerRepository;

    /**
     * VippsAccountManagement constructor.
     *
     * @param VippsCustomerInterfaceFactory $vippsCustomerFactory
     * @param VippsCustomerRepositoryInterface $vippsCustomerRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        VippsCustomerInterfaceFactory $vippsCustomerFactory,
        VippsCustomerRepositoryInterface $vippsCustomerRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->vippsCustomerFactory = $vippsCustomerFactory;
        $this->vippsCustomerRepository = $vippsCustomerRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Link existing Magento Customer to vipps User.
     *
     * @param UserInfoInterface $userInfo
     * @param CustomerInterface $customer
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function link(UserInfoInterface $userInfo, CustomerInterface $customer)
    {
        /** @var VippsCustomerInterface $vippsCustomer */
        $vippsCustomer = $this->vippsCustomerFactory->create();

        $vippsCustomer->setCustomerEntityId($customer->getId());
        $vippsCustomer->setWebsiteId($this->storeManager->getWebsite()->getWebsiteId());
        $vippsCustomer->setEmail($customer->getEmail());
        $vippsCustomer->setTelephone($userInfo->getPhoneNumber());
        $vippsCustomer->setLinked(true);

        $this->vippsCustomerRepository->save($vippsCustomer);
    }

    public function unlink()
    {
        // TODO: Implement unlink() method.
    }
}
