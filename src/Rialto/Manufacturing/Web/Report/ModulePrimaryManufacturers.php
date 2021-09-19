<?php

namespace Rialto\Manufacturing\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Stock\Category\StockCategory;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

class ModulePrimaryManufacturers extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [
            Role::MANUFACTURING,
            Role::PURCHASING_DATA
        ];
    }

    public function getTables(array $params): array
    {
        $tables = [];
        $moduleCategory = StockCategory::MODULE;
        $table = new RawSqlAudit("Geppetto Module Primary Manufacturers",
            "SELECT
                moduleItem.StockID AS SKU,
                moduleItem.Description AS Name,
                autoBuildVersion.version as Version,
                primaryBOM.Component as PrimaryComponentSKU,
                manufacturer.name as Manufacturer,
                preferredPurchasingData.ManufacturerCode as MPN
            FROM StockMaster AS moduleItem
            LEFT JOIN ItemVersion AS autoBuildVersion
                ON autoBuildVersion.stockCode = moduleItem.StockID
                AND autoBuildVersion.version = moduleItem.AutoBuildVersion
            LEFT JOIN BOM as primaryBOM
                ON primaryBOM.Parent = autoBuildVersion.stockCode
                AND primaryBOM.ParentVersion = autoBuildVersion.version
                AND primaryBOM.isPrimary = TRUE
            LEFT JOIN PurchData as preferredPurchasingData
                ON preferredPurchasingData.StockID = primaryBOM.Component
                AND preferredPurchasingData.Preferred = TRUE
            LEFT JOIN Manufacturer as manufacturer
                ON manufacturer.id = preferredPurchasingData.manufacturerID
            WHERE moduleItem.CategoryID = $moduleCategory
            ORDER BY SKU");

        $table->setAlias('PrimaryComponentSKU', 'Primary Component SKU');
        $table->setLink('SKU', 'stock_item_view', function (array $row) {
            return ['item' => $row['SKU']];
        });
        $table->setLink('Version', 'item_version_edit', function (array $row) {
            return [
                'item' => $row['SKU'],
                'version' => $row['Version']
            ];
        });
        $table->setLink('PrimaryComponentSKU', 'stock_item_view', function (array $row) {
            return ['item' => $row['PrimaryComponentSKU']];
        });
        $tables[] = $table;
        return $tables;
    }
}