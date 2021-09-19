<?php

namespace Rialto\Accounting\Web\Report;

use Doctrine\DBAL\Connection;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Web\Report\AuditColumn;
use Rialto\Web\Report\AuditTable;

/**
 * A tax audit report for looking at the amount of money that moved into
 * the work in process (WIP) account.
 */
class WipTaxAudit
implements AuditTable
{
    private $results = [];
    private $columns = [];

    public function getColumns()
    {
        return $this->columns;
    }

    public function getDescription()
    {
        return '';
    }

    public function getKey()
    {
        return str_replace(' ', '_', $this->getTitle());
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getTitle()
    {
        return 'Work in process';
    }

    public function getTotal()
    {
        $total = 0;
        foreach ( $this->results as $result ) {
            $total += $result['ValueOutstanding'];
        }
        return $total;
    }

    public function loadResults(DoctrineDbManager $dbm, array $params)
    {
        $conn = $dbm->getEntityManager()->getConnection();
        $issueVal = $this->loadValueIssued($conn, $params);
        $issueQty = $this->loadQtyIssued($conn, $params);
        $recQty = $this->loadQtyReceived($conn, $params);
        $this->results = $this->combineData($issueVal, $issueQty, $recQty);

        $this->columns = [];
        $this->createColumn('WorkOrderID');
        $this->createColumn('Location');
        $this->createColumn('QtyIssued')->setScale(0);
        $this->createColumn('QtyReceived')->setScale(0);
        $this->createColumn('ValueIssued')->setScale(2);
        $this->createColumn('ValueReceived')->setScale(2);
        $this->createColumn('ValueOutstanding')->setScale(2);
    }

    private function createColumn($key)
    {
        $col = new AuditColumn($key);
        $this->columns[$key] = $col;
        return $col;
    }

    private function loadValueIssued(Connection $conn, array $params)
    {
        $sql = "select wo.id as WorkOrderID,
            loc.LocationName as Location,
            sum(((ii.unitQtyIssued * i.qtyIssued) + ii.scrapIssued) * ii.UnitStandardCost)
            as ValueIssued
            from StockProducer wo
            join PurchOrders po on wo.purchaseOrderID = po.OrderNo
            join Locations loc on po.locationID = loc.LocCode
            join WOIssues i on i.WorkOrderID = wo.id
            join WOIssueItems ii on ii.IssueID = i.IssueNo
            where wo.type = 'labour'
            and i.IssueDate <= :yearEnd
            and (wo.dateClosed > :yearEnd or wo.dateClosed is null)
            group by wo.id";

        $stmt = $conn->executeQuery($sql, [
            'yearEnd' => $params['yearEnd']
        ]);
        return $stmt->fetchAll();
    }

    private function loadQtyIssued(Connection $conn, array $params)
    {
        $sql = "select wo.id as WorkOrderID,
            sum(i.qtyIssued) as QtyIssued
            from StockProducer wo
            join WOIssues i on i.WorkOrderID = wo.id
            where wo.type = 'labour'
            and i.IssueDate <= :yearEnd
            and (wo.dateClosed > :yearEnd or wo.dateClosed is null)
            group by wo.id";

        $stmt = $conn->executeQuery($sql, [
            'yearEnd' => $params['yearEnd']
        ]);
        return $stmt->fetchAll();
    }

    private function loadQtyReceived(Connection $conn, array $params)
    {
        $sql = "select wo.id as WorkOrderID,
            sum(ifnull(sm.quantity, 0)) as QtyReceived
            from StockProducer wo
            join PurchData purchData on wo.purchasingDataID = purchData.ID
            left join GoodsReceivedNotice grn on grn.PurchaseOrderNo = wo.purchaseOrderID
            left join StockMove sm
            on (sm.systemTypeID = :woReceipt and sm.systemTypeNumber = wo.id)
            or (sm.systemTypeID = :grn and sm.systemTypeNumber = grn.BatchID)
            and sm.stockCode = purchData.StockID
            where wo.type = 'labour'
            and (sm.dateMoved <= :yearEnd or sm.dateMoved is null)
            and (wo.dateClosed > :yearEnd or wo.dateClosed is null)
            group by wo.id";

        $stmt = $conn->executeQuery($sql, [
            'yearEnd' => $params['yearEnd'],
            'woReceipt' => SystemType::WORK_ORDER_RECEIPT_LEGACY,
            'grn' => SystemType::PURCHASE_ORDER_DELIVERY,
        ]);
        return $stmt->fetchAll();
    }

    private function combineData(array $issueVal, array $issueQty, array $recQty)
    {
        $issued = $this->createIndex($issueQty, 'QtyIssued');
        $received = $this->createIndex($recQty, 'QtyReceived');

        $finalResults = [];
        foreach ( $issueVal as $result ) {
            $woId = $result['WorkOrderID'];
            $valueIssued = $result['ValueIssued'];
            $qtyIssued = $issued[$woId];
            $qtyReceived = isset($received[$woId]) ? $received[$woId] : 0;
            $percentReceived = ($qtyIssued == 0) ? 0 : $qtyReceived / $qtyIssued;
            $valueReceived = $valueIssued * $percentReceived;
            $valueOutstanding = $valueIssued - $valueReceived;
            if ( round($valueOutstanding, 4) == 0 ) continue;

            $result['QtyIssued'] = $qtyIssued;
            $result['QtyReceived'] = $qtyReceived;
            $result['ValueReceived'] = $valueReceived;
            $result['ValueOutstanding'] = $valueOutstanding;
            $finalResults[] = $result;
        }
        return $finalResults;
    }

    private function createIndex(array $results, $field)
    {
        $index = [];
        foreach ( $results as $result ) {
            $woId = $result['WorkOrderID'];
            $index[$woId] = $result[$field];
        }
        return $index;
    }
}
