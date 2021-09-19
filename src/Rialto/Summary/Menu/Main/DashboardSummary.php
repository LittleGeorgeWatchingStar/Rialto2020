<?php

namespace Rialto\Summary\Menu\Main;

use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Summary\Menu\SummaryLink;

/**
 * Summary node for the "Dashboards" side menu group.
 */
class DashboardSummary extends OrmNodeAbstract
{
    protected function loadChildren(): array
    {
        return array_merge(
            $this->getSupplierDashboards(),
            $this->getWarehouseDashboards(),
            $this->getManufacturingDashboards(),
            $this->getShippingDashboards()
        );
    }

    private function getSupplierDashboards()
    {
        $links = [];
        $suppliers = $this->fetchSupplierData();
        foreach ($suppliers as $data) {
            $uri = $this->router->generate('supplier_order_list', [
                'id' => $data['id'],
            ]);
            $links[] = new SummaryLink($data['id'], $uri, $data['name']);
        }
        return $links;
    }

    private function fetchSupplierData()
    {
        $qb = $this->dbm->createQueryBuilder();
        $qb->select('s.id, s.name')
            ->from(Supplier::class, 's')
            ->join('s.facility', 'l')
            ->andWhere('l.active = 1')
            ->orderBy('s.name');
        return $qb->getQuery()->getArrayResult();
    }

    private function getWarehouseDashboards()
    {
        $hq = Facility::fetchHeadquarters($this->dbm);
        $uri = $this->router->generate('warehouse_dashboard');
        return [
            new SummaryLink('warehouse', $uri, $hq->getName()),
        ];
    }

    private function getManufacturingDashboards()
    {
        $uri = $this->router->generate('manufacturing_dashboard');
        return [
            new SummaryLink('manufacturing', $uri, 'All production'),
        ];
    }

    private function getShippingDashboards()
    {
        $uri = $this->router->generate('shipping_dashboard');
        return [
            new SummaryLink('shipping', $uri, 'Shipping'),
        ];
    }

    public function getId(): string
    {
        return 'Dashboards';
    }

    public function getLabel(): string
    {
        return $this->getId();
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::MANUFACTURING,
            Role::STOCK,
            Role::ENGINEER,
            Role::PURCHASING,
            Role::WAREHOUSE,
        ];
    }
}
