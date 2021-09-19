<?php

namespace Rialto\Stock\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Stock\Category\StockCategory;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 *
 */
class UnusedParts extends BasicAuditReport
{
    protected function getDefaultParameters(array $query): array
    {
        return [
            'threshold' => 2,
            'partCategory' => StockCategory::PART,
            'parentStatus' => 0,
        ];
    }

    public function getTables(array $params): array
    {
        $tables = [];
        $table = new RawSqlAudit("Unused parts",
            "select part.StockID as stockCode,
            sum(bin.Quantity) as qtyInStock,
            count(distinct bin.SerialNo) as numBins,
            count(distinct bomItem.Parent) as numParents,
            group_concat(distinct bomItem.Parent) as parents,
            ifnull(cost.materialCost + cost.labourCost + cost.overheadCost, 0) as unitStdCost,
            ifnull(cost.materialCost + cost.labourCost + cost.overheadCost, 0) *
                sum(bin.Quantity) as totalStdCost
            from StockMaster part
            left join BOM bomItem
                on part.StockID = bomItem.Component
            left join StockMaster parent
                on bomItem.Parent = parent.StockID
            join StockSerialItems bin
                on part.StockID = bin.StockID
            left join StandardCost cost
                on part.currentStandardCost = cost.id
            where part.CategoryID = :partCategory
            and (parent.Discontinued is null or parent.Discontinued >= :parentStatus)
            and bin.Quantity > 0
            group by part.StockID
            having numParents <= :threshold
            and qtyInStock > 0
            order by numParents asc, stockCode asc");
        $table->setScale('qtyInStock', 0);
        $table->setScale('numBins', 0);
        $table->setScale('numParents', 0);
        $table->setScale('unitStdCost', 4);
        $table->setScale('totalStdCost', 4);

        $tables[] = $table;
        return $tables;
    }

    public function getAllowedRoles()
    {
        return [Role::ENGINEER];
    }
}
