<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Websolute\TransporterMagentoOrder\Model\GetOrderToSendCollection;

class SetOldOrdersTransporterExportedToIgnoredPatch implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * This patch, set for all 'old' (before the TransporterMagentoOrder installation) orders,
     * their attribute transporter_exported to 'ignored'
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resourceConnection
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $tableName = $this->resourceConnection->getTablePrefix() . 'sales_order';
        $tableName = $this->resourceConnection->getTableName($tableName);
        $connection = $this->resourceConnection->getConnection();
        $connection->update($tableName, [
            GetOrderToSendCollection::TRANSPORTER_EXPORTED => 'ignored'
        ]);

        $this->moduleDataSetup->endSetup();
    }
}
