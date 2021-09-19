<?php

namespace Rialto\Supplier\Order\Web\Facades;


use Rialto\Allocation\Requirement\RequirementTask\RequirementTaskFactory;
use Rialto\Manufacturing\PurchaseOrder\OrderStatusIndex;
use Rialto\Manufacturing\ClearToBuild\ClearToBuildFactory;
use Rialto\Purchasing\Order\PurchaseOrder;
use Twig\Environment;

class OrderStatusIndexFacade
{
    /** @var OrderStatusIndex */
    private $orderStatusIndex;

    /** @var RequirementTaskFactory */
    private $factory;

    /** @var ClearToBuildFactory */
    private $clearToBuild;

    /** @var Environment */
    private $twig;

    public function __construct(OrderStatusIndex $orderStatusIndex, RequirementTaskFactory $factory, ClearToBuildFactory $clearToBuild, Environment $twig)
    {
        $this->orderStatusIndex = $orderStatusIndex;
        $this->factory = $factory;
        $this->clearToBuild = $clearToBuild;
        $this->twig = $twig;
    }

    public function getStatuses()
    {
        $statuses = [];

        foreach ($this->orderStatusIndex as $status => $purchaseOrders)
        {
            $statuses[$status] = array_map(function (PurchaseOrder $purchaseOrder) {
                return $purchaseOrder->getId();
            }, $purchaseOrders);
        }
        return $statuses;
    }
}