<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vipps\Login\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->getConnection()->addColumn(
            $setup->getTable('customer_entity'),
            'vipps_telephone',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Vipps Telephone',
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('customer_entity'),
            'vipps_linked',
            [
                'type' => Table::TYPE_BOOLEAN,
                'comment' => 'Vipps Linked'
            ]
        );

        $setup->endSetup();
    }
}
