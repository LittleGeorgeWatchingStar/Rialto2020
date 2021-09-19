<?php

namespace Rialto\Summary\Menu\Main;

use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\Role\Role;
use Rialto\Summary\Menu\SummaryLink;

/**
 * Renders the "orders to kit" link in the summary menu.
 */
class OrdersToKitSummary extends OrmNodeAbstract
{
    public function getId(): string
    {
        return 'OrdersToKit';
    }

    public function getLabel(): string
    {
        return 'Orders to kit';
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::MANUFACTURING,
            Role::WAREHOUSE,
        ];
    }

    protected function loadChildren(): array
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
        /** @var $repo PurchaseOrderRepository */
        $repo = $this->dbm->getRepository(PurchaseOrder::class);
        return $repo->findOrdersToKitSummary();
    }

    private function buildLink(array $row)
    {
        $id = $row['id'];

        $uri = $this->router->generate(
            'manufacturing_kit_create', ['id' => $id]);

        $text = sprintf('%s (%s)', $row['name'], $row['numOrders']);

        return new SummaryLink($id, $uri, $text);
    }
}
