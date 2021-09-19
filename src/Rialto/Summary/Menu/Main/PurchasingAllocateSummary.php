<?php

namespace Rialto\Summary\Menu\Main;

use Rialto\Manufacturing\WorkOrder\Orm\WorkOrderRepository;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Security\Role\Role;
use Rialto\Summary\Menu\SummaryLink;


class PurchasingAllocateSummary extends OrmNodeAbstract
{
    public function getId(): string
    {
        return 'PurchasingAllocate';
    }

    public function getLabel(): string
    {
        return 'Allocate';
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::ADMIN,
        ];
    }

    protected function loadChildren(): array
    {
        $links = [];
        foreach ($this->fetchData() as $data) {
            $id = $data['supplierID'];
            $name = $data['supplierName'];
            $num = $data['numOrders'];
            $links[] = new SummaryLink(
                $this->getId() . $id,
                $this->router->generate('supplier_order_list', [
                    'id' => $id,
                ]),
                sprintf('%s (%s)', $name, number_format($num)));
        }
        return $links;
    }

    private function fetchData()
    {
        /** @var $repo WorkOrderRepository */
        $repo = $this->dbm->getRepository(WorkOrder::class);
        return $repo->findOrdersNeedingAllocationSummary();
    }
}
