<?php

namespace Rialto\Summary\Menu\Main;

use Rialto\Security\Role\Role;
use Rialto\Summary\Menu\SummaryLink;

/**
 * Number of POs to send, grouped by supplier.
 */
class PurchasingSendSummary extends DBNodeAbstract
{
    public function getAllowedRoles(): array
    {
        return [Role::ADMIN];
    }

    public function getId(): string
    {
        return 'PurchasingOrder';
    }

    public function getLabel(): string
    {
        return 'Send PO';
    }

    protected function loadChildren()
    {
        $links = [];
        foreach ( $this->fetchData() as $result ) {
            $num = $result['numOrders'];
            $id = $result['supplierID'];
            $name = $result['supplierName'];

            $uri = $this->router->generate(
                'purchase_order_list', [
                    'supplier' => $id,
                    'completed' => 'no',
                    'printed' => 'no',
            ]);
            $label = sprintf('%s (%s)', $name, number_format($num));

            $links[] = new SummaryLink($this->getId() . $id, $uri, $label);
        }
        return $links;
    }

    private function fetchData()
    {
        $sql = "SELECT count(DISTINCT po.OrderNo) as numOrders,
            supplier.SupplierID as supplierID,
            supplier.SuppName as supplierName
            FROM PurchOrders po
            JOIN Suppliers supplier
               on po.SupplierNo = supplier.SupplierID
            JOIN StockProducer sp
               ON po.OrderNo = sp.purchaseOrderID
            WHERE sp.dateClosed is null
               AND sp.qtyReceived < sp.qtyOrdered
               AND po.DatePrinted is null
            group by supplier.SupplierID";

        $stmt = $this->db->executeQuery($sql);
        return $stmt->fetchAll();
    }

}
