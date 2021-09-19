<?php

namespace Rialto\Manufacturing\Task;


use Rialto\Manufacturing\WorkOrder\WorkOrderCollection;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Task\Task;


/**
 * A task that needs to be done to complete a purchase order.
 */
class ProductionTask extends Task
{
    const REQUIRED = 'required';
    const WAITING = 'waiting';
    const OPTIONAL = 'optional';

    /** @var PurchaseOrder */
    private $purchaseOrder;

    public function __construct($name = null, $routeName = null, array $routeParams = [], array $roles = [])
    {
        parent::__construct($name, $routeName, $routeParams, $roles);
        $this->setStatus(self::REQUIRED);
    }

    public function setPurchaseOrder(PurchaseOrder $order)
    {
        $this->purchaseOrder = $order;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getName() == 'Due'
            ? $this->renderDueDate()
            : $this->getName();
    }

    private function renderDueDate()
    {
        $wos = WorkOrderCollection::fromPurchaseOrder($this->purchaseOrder);
        $date = $wos->getNextOutstandingCommitmentDate();
        if (null === $date) {
            return 'none';
        }
        return $date->format('Y-m-d');
    }

    public function setOptional()
    {
        return $this->setStatus(self::OPTIONAL);
    }

    public function setWaiting()
    {
        return $this->setStatus(self::WAITING);
    }

    public function setRequired()
    {
        return $this->setStatus(self::REQUIRED);
    }
}
