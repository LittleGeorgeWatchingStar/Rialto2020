<?php

namespace Rialto\Manufacturing\Web\Report;

use Rialto\Stock\Category\StockCategory;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 *
 */
class BagsAndBoms extends BasicAuditReport
{
    protected function getDefaultParameters(array $query): array
    {
        return [
            'boardCategory' => StockCategory::BOARD,
        ];
    }

    public function getTables(array $params): array
    {
        $reports = [];

        $report = new RawSqlAudit("BOMs with bags",
            "select distinct n.Parent, n.ParentVersion, n.Component
            from BOM n
            join StockMaster i on n.Parent = i.StockID
            join ItemVersion v on n.Parent = v.stockCode and n.ParentVersion = v.version
            where n.Component like 'bag%'
            and i.CategoryID = :boardCategory
            and i.Discontinued = 0
            and v.active = 1
            order by n.Parent, n.ParentVersion");

        $reports[] = $report;

        $report = new RawSqlAudit("Boards without bags",
            "select distinct n.Parent, n.ParentVersion
            from BOM n left join (
                select distinct Parent, ParentVersion
                from BOM
                where Component like 'bag%'
            ) h
                on n.ParentVersion = h.ParentVersion and n.Parent = h.Parent
            join StockMaster i on n.Parent = i.StockID
            join ItemVersion v on n.Parent = v.stockCode and n.ParentVersion = v.version
            where h.Parent is null
            and i.CategoryID = :boardCategory
            and i.Discontinued = 0
            and v.active = 1
            order by n.Parent, n.ParentVersion");

        $reports[] = $report;

        return $reports;
    }

}
