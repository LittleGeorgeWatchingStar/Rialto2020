<?php

namespace Rialto\Stock\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Stock\Category\StockCategory;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * A report of all the parts required for electrical engineering in Eagle.
 */
class EagleParts extends BasicAuditReport
{
    protected function getDefaultParameters(array $query): array
    {
        return [
            'category' => StockCategory::PART,
        ];
    }

    public function getTables(array $params): array
    {
        $tables = [];
        $table = new RawSqlAudit("Eagle components",
            "select i.StockID as stockCode
                , i.Package as package
                , i.PartValue as partValue
                , p.ManufacturerCode as manufacturerCode
            from StockMaster as i
            join (
                select StockID, ManufacturerCode
                from PurchData
                order by Preferred desc
            ) as p
                on p.StockID = i.StockID
            where i.CategoryID = :category
            and i.Package != ''
            and i.Discontinued = 0
            group by i.StockID
            order by package, partValue
        ");
        $table->setScale('partValue', null); // not numeric

        $tables[] = $table;
        return $tables;
    }

    public function getAllowedRoles()
    {
        return [Role::ENGINEER];
    }
}
