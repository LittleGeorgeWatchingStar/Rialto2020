<?php


namespace Rialto\Manufacturing\Web\Report;


use Rialto\Web\Report\BasicAuditReport;

class ModuleBomCost extends BasicAuditReport
{
    protected function getDefaultParameters(array $params): array
    {
        return [];
    }

    public function getTables(array $params): array
    {
        $table = new ModuleBomCostTable('Module BOM Cost Audit',
            'Compare the current vs calculated costs of Module BOMs');

        $table->setLink('SKU', 'stock_item_view', function ($row) {
            return ['item' => $row['SKU']];
        });
        $table->setLink('Version', 'item_version_edit', function ($row) {
            return ['item' => $row['SKU'], 'version' => $row['Version']];
        });
        $table->setLink('Standard Cost', 'item_standard_cost', function ($row) {
            return ['item' => $row['SKU']];
        });

        return [$table];
    }
}
