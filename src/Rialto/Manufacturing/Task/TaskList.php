<?php

namespace Rialto\Manufacturing\Task;

use Rialto\Purchasing\Order\PurchaseOrder;

class TaskList implements \IteratorAggregate, \Countable
{
    /** @var PurchaseOrder */
    private $po;
    private $allowedRoles;
    private $list = [];

    public function __construct(PurchaseOrder $po, array $roles = [])
    {
        $this->po = $po;
        $this->allowedRoles = $roles;
    }

    /** @return PurchaseOrder */
    public function getPurchaseOrder()
    {
        return $this->po;
    }

    /**
     * @return string[]
     */
    public function getAllowedRoles()
    {
        return $this->allowedRoles;
    }


    public function add(ProductionTask $task = null)
    {
        if ($task) {
            $task->setPurchaseOrder($this->po);
            $task->addRoles($this->allowedRoles);
            $this->list[] = $task;
        }
    }

    /**
     * @param ProductionTask[] $tasks
     */
    public function addAll(array $tasks)
    {
        foreach ($tasks as $task) {
            $this->add($task);
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->list);
    }

    public function count()
    {
        return count($this->list);
    }

    public function merge(self $other)
    {
        foreach ($other as $task) {
            $this->list[] = $task;
        }
    }
}
