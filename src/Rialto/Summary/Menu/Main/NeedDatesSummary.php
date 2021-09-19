<?php

namespace Rialto\Summary\Menu\Main;

use Rialto\Security\Role\Role;
use Rialto\Summary\Menu\SummaryLink;

/**
 * Shows which work orders are still waiting for commitment dates, grouped
 * by manufacturer.
 */
class NeedDatesSummary extends DBNodeAbstract
{
    public function getId(): string
    {
        return 'NeedDates';
    }

    public function getLabel(): string
    {
        return 'Need dates';
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::ADMIN,
        ];
    }

    protected function loadChildren()
    {
        $links = [];
        $data = $this->fetchData();
        foreach ( $data as $row ) {
            $links[] = $this->buildLink($row);
        }
        return $links;
    }

    private function fetchData()
    {
        $sql = "SELECT count(wo.id) as numOrders,
            supplier.SuppName as supplierName,
            supplier.SupplierID as supplierID
            FROM StockProducer wo
            JOIN PurchOrders po
                on wo.purchaseOrderID = po.OrderNo
            JOIN Suppliers supplier
                on po.SupplierNo = supplier.SupplierID
            WHERE wo.type = 'labour'
            AND wo.qtyIssued > 0
            AND wo.qtyOrdered > 0
            AND wo.dateClosed is null
            AND wo.commitmentDate is null
            group by supplier.SupplierID
            ORDER BY supplier.SuppName";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    private function buildLink(array $row)
    {
        $id = $row['supplierID'];

        $uri = $this->router->generate('supplier_order_list', [
            'id' => $id,
        ]);

        $text = sprintf('%s (%s)', $row['supplierName'], number_format($row['numOrders']));

        return new SummaryLink($this->getId() . $id, $uri, $text);
    }
}
