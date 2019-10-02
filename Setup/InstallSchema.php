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
use Magento\Framework\DB\Adapter\AdapterInterface;

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
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('vipps_customer_entity')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'customer_entity_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Entity Id'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            255,
            [],
            'Email'
        )->addColumn(
            'telephone',
            Table::TYPE_TEXT,
            255,
            [],
            'Vipps Telephone'
        )->addColumn(
            'linked',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Active'
        )->addIndex(
            $installer->getIdxName('vipps_customer_entity', ['telephone']),
            ['telephone']
        )->addForeignKey(
            $installer->getFkName('vipps_customer_entity', 'entity_id', 'customer_entity', 'entity_id'),
            'entity_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Vipps Customer Entity'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
