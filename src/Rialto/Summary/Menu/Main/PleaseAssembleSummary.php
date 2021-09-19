<?php

namespace Rialto\Summary\Menu\Main;

use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Manufacturing\WorkOrder\Orm\WorkOrderRepository;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Summary\Menu\SummaryLink;
use Rialto\Summary\Menu\SummaryNode;
use Symfony\Component\Routing\RouterInterface;

/**
 * Summary for work orders to be assembled in-house.
 */
class PleaseAssembleSummary extends OrmNodeAbstract
{
    public function getId(): string
    {
        return 'PleaseAssemble';
    }

    public function getLabel(): string
    {
        return 'Please assemble';
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
        $orders = $this->fetchData();
        foreach ($orders as $order) {
            $links[] = new PleaseAssembleNode($order, $this->router);
        }
        return $links;
    }

    private function fetchData()
    {
        /* @var $repo WorkOrderRepository */
        $repo = $this->dbm->getRepository(WorkOrder::class);
        $hq = Facility::fetchHeadquarters($this->dbm);
        return $repo->findReceivableOrders($hq);
    }
}

class PleaseAssembleNode implements SummaryNode
{
    /** @var WorkOrder */
    private $workOrder;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        WorkOrder $order,
        RouterInterface $router)
    {
        $this->workOrder = $order;
        $this->router = $router;
    }

    public function getId(): string
    {
        return 'PleaseAssemble' . $this->workOrder->getId();
    }

    public function getLabel(): string
    {
        return $this->workOrder->getSku();
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::MANUFACTURING,
            Role::WAREHOUSE,
        ];
    }

    public function getChildren(): array
    {
        $id = $this->workOrder->getId();
        $links = [];

        $links[] = new SummaryLink(
            sprintf('instructions%s', $id),
            $this->router->generate('Manufacturing_WorkOrder_instructions', [
                'id' => $id,
            ]),
            "Instructions"
        );

        $status = RequirementStatus::forConsumer($this->workOrder);
        if ($status->getQtyAllocated() > 0) {
            $links[] = new SummaryLink(
                sprintf('parts%s', $id),
                $this->router->generate('manufacturing_workorder_components', [
                    'id' => $id,
                ]),
                "Parts"
            );
        }
        if (! $status->isFullyAllocated()) {
            $links[] = new SummaryLink(
                sprintf('allocate%s', $id),
                $this->router->generate('work_order_allocate', [
                    'id' => $id,
                ]),
                'Allocate parts');
        }
        $prep = $this->workOrder->getPrepWorkAtLocation($this->workOrder->getLocation());
        if (count($prep) > 0) {
            $prep = reset($prep);
            $links[] = new SummaryLink(
                sprintf('prep%s', $id),
                $this->router->generate('work_order_view', [
                    'order' => $id,
                ]),
                sprintf('Prep %s', $prep->getSku()));
        }
        $stockItem = $this->workOrder->getStockItem();
        if ($stockItem->isPrintedLabel()) {
            $links[] = new SummaryLink(
                sprintf('print%s', $id),
                $this->router->generate('work_order_view', [
                    'order' => $id,
                ]),
                sprintf('Print for %s', $stockItem->getSku()));
        }
        $receivable = $this->workOrder->getQtyRemaining();
        $links[] = new SummaryLink(
            sprintf('receive%s', $id),
            $this->router->generate('receive_po', [
                'id' => $this->workOrder->getOrderNumber(),
            ]),
            sprintf('Build %s units', number_format($receivable))
        );

        return $links;
    }
}
