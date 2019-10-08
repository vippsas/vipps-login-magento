<?php
/**
 * Copyright 2018 Vipps
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 *  documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 *  the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 *  and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 *  TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 *  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 *  CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 *  IN THE SOFTWARE.
 */

namespace Vipps\Login\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SetupInterface;

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

        $this->createVippsCustomerTable($installer);

        $this->createVippsAddressTable($installer);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     *
     * @throws \Zend_Db_Exception
     */
    private function createVippsCustomerTable(SchemaSetupInterface $installer)
    {
        $vippsCustomerEntityTableName = $installer->getConnection()
            ->getTableName('vipps_customer_entity');

        $customerEntityTableName = $installer->getConnection()
            ->getTableName('customer_entity');

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
            'website_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Website Id'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Email'
        )->addColumn(
            'telephone',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Vipps Telephone'
        )->addColumn(
            'linked',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Is Active'
        )->addColumn(
            'confirmation_key',
            Table::TYPE_TEXT,
            255,
            [],
            'Confirmation Key'
        )->addColumn(
                'confirmation_exp',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Confirmation Expiration Time'
        )->addIndex(
            $installer->getIdxName($vippsCustomerEntityTableName, ['telephone', 'website_id', 'linked']),
            ['telephone', 'website_id', 'linked']
        )->addIndex(
            $installer->getIdxName(
                'vipps_customer_entity',
                ['customer_entity_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['customer_entity_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName(
                $vippsCustomerEntityTableName,
                'customer_entity_id',
                $customerEntityTableName,
                'entity_id'
            ),
            'customer_entity_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Vipps Customer Entity'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     *
     * @throws \Zend_Db_Exception
     */
    private function createVippsAddressTable(SchemaSetupInterface $installer)
    {
        $vippsCustomerAddressTable = $installer->getConnection()
            ->getTableName('vipps_customer_address_entity');

        $vippsCustomerEntityTable = $installer->getConnection()
            ->getTableName('vipps_customer_entity');

        $customerAddressEntityTable = $installer->getConnection()
            ->getTableName('customer_address_entity');

        $table = $installer->getConnection()->newTable(
            $installer->getTable('vipps_customer_entity')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'vipps_customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Address Entity Id'
        )->addColumn(
            'customer_address_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Customer Address Entity Id'
        )->addColumn(
            'country',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'NO'],
            'Country ID '
        )->addColumn(
            'street_address',
            Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vipps street address'
        )->addColumn(
            'address_type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => true, 'nullable' => false, 'default' => 'home'],
            'Vipps address type'
        )->addColumn(
            'formatted',
            Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'formatted street address'
        )->addColumn(
            'postal_code',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => true, 'nullable' => false],
            'Zip/Postal Code'
        )->addColumn(
            'region',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'State/Province'
        )->addColumn(
            'is_default',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Is Active'
        )->addColumn(
            'is_converted',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Is Vipps Address converted to Magento Address'
        )->addForeignKey(
            $installer->getFkName(
                $vippsCustomerAddressTable,
                'vipps_customer_id',
                $vippsCustomerEntityTable,
                'entity_id'
            ),
            'vipps_customer_id',
            $vippsCustomerEntityTable,
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Vipps Customer Address Entity'
        );

        $installer->getConnection()->createTable($table);
    }
}
