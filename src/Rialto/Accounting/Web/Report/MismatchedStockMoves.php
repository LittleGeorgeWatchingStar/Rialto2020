<?php

namespace Rialto\Accounting\Web\Report;

use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Offers views of stock moves that have gone negative or don't match
 * expected stock levels.
 */
class MismatchedStockMoves
extends BasicAuditReport
{
    protected function getDefaultParameters(array $query): array
    {
        return [];
    }

    public function getTables(array $params): array
    {
        $tables = [];
        $table = new RawSqlAudit("Unbalanced moves by bin",
            "select bin.SerialNo
                , bin.StockID
                , loc.LocationName as Location
                , sum(move.quantity) MoveQty
                , bin.Quantity as BinQty
                , sum(move.quantity) - bin.Quantity as QtyDiff
                , date(max(move.dateMoved)) as LastMove
                , (sum(move.quantity) - bin.Quantity) * (cost.materialCost + cost.labourCost + cost.overheadCost) as ValueDiff
                from StockMove move
                join StockSerialItems bin
                    on move.binID = bin.SerialNo
                    and move.stockCode = bin.StockID
                join Locations loc
                    on bin.LocCode = loc.LocCode
                left join StockMaster item
                    on bin.StockID = item.StockID
                left join StandardCost cost
                    on item.currentStandardCost = cost.id
                group by bin.SerialNo
                having BinQty != MoveQty
                order by StockID, Location, SerialNo");
        $table->setLink('MoveQty', 'stock_move_list', function($row) {
            return [
                'bin' => $row['SerialNo'],
                '_limit' => 0,
            ];
        });
        $table->setScale("MoveQty", 0);
        $table->setScale("BinQty", 0);
        $tables[] = $table;

        $table = new RawSqlAudit("Unbalanced moves by bin and location",
            "select bin.SerialNo as binId
                , bin.StockID as sku
                , move.locationID
                , loc.LocationName as location
                , ifnull(move.total, 0) as moveQty
                , if(bin.LocCode = move.locationID, bin.Quantity, 0) as binQty
                , ifnull(move.total, 0) - if(bin.LocCode = move.locationID, bin.Quantity, 0) as diff
                from (
                    select binID
                    , stockCode
                    , locationID
                    , sum(quantity) as total
                    from StockMove
                    group by binID, stockCode, locationID
                ) move
                join StockSerialItems bin
                    on move.binID = bin.SerialNo
                    and move.stockCode = bin.StockID
                left join Locations loc
                    on move.locationID = loc.LocCode
                having moveQty != binQty
                order by sku, SerialNo, location");

        $table->setLink('moveQty', 'stock_move_list', function($row) {
            return [
                'bin' => $row['binId'],
                'location' => $row['locationID'],
                '_limit' => 0,
            ];
        });
        $table->setLink('binQty', 'stock_move_list', function($row) {
            return [
                'bin' => $row['binId'],
                '_limit' => 0,
            ];
        });
        $table->setScale("moveQty", 0);
        $table->setScale("binQty", 0);
        $table->setScale("diff", 0);
        $tables[] = $table;

        $table = new RawSqlAudit("Unbalanced bin moves by location",
            "select bin.StockID
                , loc.LocCode as LocationID
                , loc.LocationName
                , move.MoveQty
                , bin.BinQty
                , move.MoveQty - bin.BinQty as QtyDiff
                from (
                    select sum(Quantity) as BinQty,
                    StockID,
                    LocCode as Location
                    from StockSerialItems
                    group by StockID, Location
                ) as bin
                join (
                    select sum(quantity) as MoveQty,
                    stockCode as StockID,
                    locationID as Location
                    from StockMove
                    where StockMove.binID is not null
                    group by stockCode, locationID
                ) as move
                    on bin.StockID = move.StockID
                    and bin.Location = move.Location
                join StockMaster item
                    on bin.StockID = item.StockID
                left join Locations loc
                     on bin.Location = loc.LocCode
                where bin.BinQty != move.MoveQty
                order by StockID, LocationName");
        $table->setLink('MoveQty', 'stock_move_list', function(array $row) {
            return [
                'location' => $row['LocationID'],
                'item' => $row['StockID'],
                '_limit' => 0,
            ];
        });
        $table->setScale("MoveQty", 0);
        $table->setScale("BinQty", 0);
        $table->setScale("QtyDiff", 0);
//        $tables[] = $table;

        $table = new RawSqlAudit("Uncontrolled stock moves going negative",
            "select sum(move.Quantity) as MoveQty,
                move.stockCode,
                move.locationID
                from StockMove move
                group by move.stockCode, move.LocationID
                having MoveQty < 0
                order by stockCode, locationID");
        $table->setScale("MoveQty", 0);
//        $tables[] = $table;

        return $tables;
    }

}
