<?php


namespace Rialto\Purchasing\Web\Report;


use Rialto\Security\Role\Role;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

class PcbFabOrderReport extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::PURCHASING, Role::MANUFACTURING];
    }

    public function getTables(array $params): array
    {
        $tables = [];

        $table = new RawSqlAudit('PCB Fab Costs',
            'SELECT sp.purchaseOrderID as PO, sup.SuppName as Supplier,
                (SELECT SUM(expectedUnitCost * qtyReceived) 
                    FROM StockProducer 
                    WHERE purchaseOrderID = sp.purchaseOrderID 
                    AND (description LIKE "%NRE%"
                        OR description LIKE "%TEST%"
                        OR description LIKE "%MANUFACTURING%FEE%")
                    AND purchasingDataID IS NULL) 
                AS "NRE Fees",
                CONCAT(pd.StockID, "v", sp.version) as PCB, 
                sp.QtyInvoiced as Qty,
                (SELECT DATEDIFF(MIN(grn.DeliveryDate), MIN(os.dateSent))
                    FROM OrderSent os
                    JOIN GoodsReceivedNotice grn ON os.purchaseOrderId = grn.PurchaseOrderNo
                    WHERE os.purchaseOrderId = sp.purchaseOrderID)
                AS "Lead Time",
                iv.dimensionX as Width, iv.dimensionY as Height,
                sp.actualUnitCost as UnitCost, 
                sp.actualUnitCost / (iv.dimensionX * iv.dimensionY) AS "UnitCostPerCm^2"
                FROM PurchData pd 
                JOIN StockProducer sp ON sp.purchasingDataID = pd.ID 
                JOIN ItemVersion iv ON (iv.stockCode = pd.StockID AND iv.version = sp.version) 
                JOIN Suppliers sup ON pd.SupplierNo = sup.SupplierID
                WHERE pd.StockID LIKE "PCB9%" 
                AND actualUnitCost > 0 
                AND iv.dimensionX > 0 AND iv.dimensionY > 0');
        $table->setLink('PO', 'purchase_order_view', function (array $row) {
            return [
                'order' => $row['PO'],
            ];
        });

        $tables[] = $table;
        return $tables;
    }
}