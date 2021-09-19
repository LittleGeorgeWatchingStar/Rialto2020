<?php

namespace Rialto\Summary\Menu\Main;

use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Security\Role\Role;
use Rialto\Summary\Menu\SummaryLink;

/**
 * Renders the sales summary links.
 */
class SalesSummary extends OrmNodeAbstract
{
    public function getId(): string
    {
        return 'Sales';
    }

    public function getLabel(): string
    {
        return 'Sales';
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::EMPLOYEE,
        ];
    }

    protected function loadChildren(): array
    {
        $links = [];
        $data = $this->fetchData();
        foreach ($data as $row) {
            $links[] = $this->buildLink($row);
        }
        return $links;
    }

    private function fetchData()
    {
        /* @var $repo SalesOrderRepository */
        $repo = $this->dbm->getRepository(SalesOrder::class);
        return $repo->getOrderStatusSummary();
    }

    private function buildLink(array $row)
    {
        $id = $row['typeID'];

        $uri = $this->router->generate('sales_order_list', [
            'type' => $id,
            'salesStage' => SalesOrder::ORDER,
            'shipped' => 'no',
        ]);

        $text = sprintf('%s (%s/%s)',
            $row['typeName'],
            $row['toShip'],
            $row['numOrders']
        );

        return new SummaryLink($id, $uri, $text);
    }
}
