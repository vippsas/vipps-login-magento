<?php

namespace Vipps\Login\Setup;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $setup->startSetup();
        $attributesInfo = [
            'vipps_telephone' => [
                'label' => 'Vipps telephone',
                'type' => 'static',
                'input' => 'varchar',
                'visible' => false,
                'required' => false,
            ],
            'vipps_linked' => [
                'label' => 'Vipps Linked',
                'type' => 'static',
                'input' => 'bool',
                'visible' => false,
                'required' => false,
            ]
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute('customer', $attributeCode, $attributeParams);
        }

        $setup->endSetup();
    }
}
