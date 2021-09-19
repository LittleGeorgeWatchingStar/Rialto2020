<?php

namespace Rialto\Manufacturing\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Stock\Category\StockCategory;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Lists of items used in Geppetto modules.
 */
class GeppettoItemList extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::EMPLOYEE];
    }

    protected function getDefaultParameters(array $query): array
    {
        return [
            'boardCategory' => StockCategory::BOARD,
            'moduleCategory' => StockCategory::MODULE,
        ];
    }

    public function getTables(array $params): array
    {
        $tables = [];

        $table = new RawSqlAudit("Geppetto Reel Item List", $this->getQuery(true));

        $table->setDescription('List of items on reels used in Geppetto Modules');
        $table->setLink('BinStyle', 'binstyle_update_purchdata', function (array $row) {
            return ['stockItem' => $row['Component']];
        });

        $tables[] = $table;

        $table = new RawSqlAudit("Geppetto Non-Reel Item List", $this->getQuery(false));

        $table->setDescription('List of items not on reels used in Geppetto Modules');
        $table->setLink('BinStyle', 'binstyle_update_purchdata', function (array $row) {
            return ['stockItem' => $row['Component']];
        });

        $tables[] = $table;

        return $tables;
    }

    private function getQuery(bool $reels): string
    {
        $operator = $reels ? 'LIKE' : 'NOT LIKE';

        return "SELECT DISTINCT n.Component, m.name as Manufacturer, cpd.ManufacturerCode as MPN, c.Package, cpd.BinStyle, count( i.StockID ) AS Count
            FROM BOM n
            JOIN StockMaster i ON n.Parent = i.StockID
            JOIN StockMaster c ON n.Component = c.StockID
            JOIN PurchData cpd ON cpd.StockID = c.StockID
            LEFT JOIN Manufacturer m ON cpd.manufacturerID = m.id
            JOIN ItemVersion v ON n.Parent = v.stockCode AND n.ParentVersion = v.version
            WHERE i.CategoryID = :moduleCategory
            AND i.Discontinued = 0
            AND v.version = ( SELECT max(Version) FROM ItemVersion dv WHERE dv.active = 1 AND dv.StockCode=i.StockID)
            AND cpd.BinStyle $operator 'reel%'
            AND cpd.Preferred = 1
            GROUP BY n.Component";
    }

}
