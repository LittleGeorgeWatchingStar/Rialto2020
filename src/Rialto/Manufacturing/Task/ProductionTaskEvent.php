<?php

namespace Rialto\Manufacturing\Task;


use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Symfony\Component\EventDispatcher\Event;

class ProductionTaskEvent extends Event
{
    /** @var TaskList */
    private $taskList;

    public function __construct(TaskList $taskList)
    {
        $this->taskList = $taskList;
    }

    /** @return WorkOrder[] */
    public function getWorkOrders()
    {
        return $this->taskList->getPurchaseOrder()->getWorkOrders();
    }

    public function addTask(ProductionTask $task)
    {
        $this->taskList->add($task);
    }
}
