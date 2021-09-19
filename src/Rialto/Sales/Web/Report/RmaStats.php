<?php

namespace Rialto\Sales\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Time\Web\DateType;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Reports number of boards RMA on a work order between specified time frame.
 */
class RmaStats extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::CUSTOMER_SERVICE];
    }

    /**
     * @return string[]
     */
    protected function getDefaultParameters(array $query): array
    {
        $thisYear = (int) date('Y');
        return [
            'startDate' => "$thisYear-01-01",
            'endDate' => date('Y-m-d'),
        ];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('startDate', DateType::class, [
                'input' => 'string',
                'label' => 'RMA Authorized Between',
            ])
            ->add('endDate', DateType::class, [
                'input' => 'string',
                'label' => 'and',
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }


    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        return [
            $this->getOpenRMA(),
            $this->getRMASummary(),
        ];
    }

    private function getOpenRMA()
    {
        $sql = "
        SELECT DATE_FORMAT(rma.dateAuthorized, '%M %d, %Y') AS 'Date Authorized'
        , rma.id AS 'RMA No.'
        , rmaDetail.originalWorkOrder AS 'Work Order No.'
        , rmaDetail.qtyAuthorized AS 'Qty Authorized'
        , rmaDetail.qtyReceived AS 'Qty Received'
        , rma.trackingNumber AS 'Tracking Number'
        FROM SalesReturn rma
        JOIN SalesReturnItem rmaDetail ON rma.id = rmaDetail.salesReturn
        WHERE rma.trackingNumber !=''
        AND rmaDetail.qtyReceived = 0
        AND rma.dateAuthorized >= :startDate
        AND rma.dateAuthorized <= :endDate
        ORDER BY rma.dateAuthorized ASC;
        ";
        $table = new RawSqlAudit('Authorized RMA', $sql);
        return $table;
    }


    private function getRMASummary()
    {
        $sql = "
            SELECT loc.LocationName AS 'CM' 
            , wo.id AS 'WO No'
            , DATE_FORMAT(rmaSummary.FirstDate, '%M %d, %Y') AS 'First RMA Date'
            , wo.qtyReceived AS Built
            , rmaSummary.QtyAuthorized AS Authorized
            , rmaSummary.QtyReceived AS Received
            , rmaSummary.QtyPassed AS Passed
            , rmaSummary.QtyFailed AS Failed
            , (rmaSummary.QtyReceived / wo.qtyReceived) * 100 AS '% Returned'
            , wo.description AS 'SKU'
            FROM StockProducer wo
            JOIN (
                SELECT rma.id AS 'RMA No'
                , min(rma.dateAuthorized) AS FirstDate
                , max(rma.dateAuthorized) AS LastDate
                , rmaDetail.originalWorkOrder AS WO
                , sum(rmaDetail.qtyAuthorized) AS QtyAuthorized
                , sum(rmaDetail.qtyReceived) AS QtyReceived
                , sum(rmaDetail.qtyPassed) AS QtyPassed
                , sum(rmaDetail.qtyFailed) AS QtyFailed
                FROM SalesReturnItem rmaDetail
                JOIN SalesReturn rma ON rmaDetail.salesReturn = rma.id
                WHERE rmaDetail.qtyPassed != rmaDetail.qtyReceived
                AND rmaDetail.qtyReceived > 0
                GROUP BY rmaDetail.originalWorkOrder) AS rmaSummary         
            ON wo.id = rmaSummary.WO
            JOIN PurchOrders po ON wo.purchaseOrderID = po.OrderNo
            JOIN Locations loc ON po.locationID = loc.LocCode 
            WHERE FirstDate >= :startDate
            AND LastDate <= :endDate
            GROUP BY wo.id
        ";
        $table = new RawSqlAudit('RMA Summary', $sql);
        $table->setScale('Built', 0);
        $table->setScale('Authorized', 0);
        $table->setScale('Received', 0);
        $table->setScale('Passed', 0);
        $table->setScale('Failed', 0);
        $table->setScale('% Returned', 2);
        return $table;
    }
}
