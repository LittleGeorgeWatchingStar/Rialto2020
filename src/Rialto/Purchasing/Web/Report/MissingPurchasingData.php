<?php

namespace Rialto\Purchasing\Web\Report;

use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\Version\Version;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Show PCBs that need to be built but don't yet have purchasing data.
 */
class MissingPurchasingData extends BasicAuditReport
{
    public function getTables(array $params): array
    {
        $tables = [];

        $sql = "
            select iv.stockCode as item, iv.version
            from ItemVersion iv
            join StockMaster item on iv.stockCode = item.StockID
            where item.CategoryID = :pcbCategory
            and not exists (
                select 1
                from PurchData pd
                where pd.StockID = item.StockID and pd.Version in (iv.version, :versionAny)
            ) and exists (
                select 1
                from Requirement req
                where req.stockCode = item.StockID and req.version = iv.version
            )
        ";
        $table = new RawSqlAudit('PCBs without purchasing data', $sql);
        $table->setDescription('There is no purchasing data that matches these versions.');
        $table->setLink('item', 'purchasing_data_list', function (array $row) {
            return ['stockItem' => $row['item']];
        });

        $tables[] = $table;
        return $tables;
    }

    protected function getDefaultParameters(array $query): array
    {
        return [
            'pcbCategory' => StockCategory::PCB,
            'versionAny' => Version::ANY,
        ];
    }

}
