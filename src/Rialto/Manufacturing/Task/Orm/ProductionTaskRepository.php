<?php

namespace Rialto\Manufacturing\Task\Orm;


use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Manufacturing\Task\ProductionTask;
use Rialto\Manufacturing\Task\TaskList;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrder;

class ProductionTaskRepository extends RialtoRepositoryAbstract
{
    /**
     * All purchase orders whose tasks are stale and need to be updated.
     * @return PurchaseOrder[]
     */
    public function findOrdersToUpdate()
    {
        $qb = $this->queryOrderTasks();
        $qb->andWhere('(item.dateUpdated > task.dateCreated or task.id is null)');
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /** @return QueryBuilder */
    private function queryOrderTasks()
    {
        $qb = $this->_em->createQueryBuilder();
        $workOrder = WorkOrder::class;
        $qb->select('po')
            ->from(PurchaseOrder::class, 'po')
            ->join('po.items', 'item')
            ->leftJoin('po.tasks', 'task')
            ->where("item instance of $workOrder")
            ->andWhere('item.dateClosed is null');
        return $qb;
    }

    /**
     * All open purchase orders whose tasks can be updated.
     *
     * @return PurchaseOrder[]
     */
    public function findAllOrders()
    {
        $qb = $this->queryOrderTasks();
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * Replace existing tasks for TaskList PO with $tasks.
     * @param TaskList $tasks
     */
    public function resetTasks(TaskList $tasks)
    {
        $this->deleteExistingTasks($tasks->getPurchaseOrder());
        foreach ($tasks as $task) {
            $this->_em->persist($task);
        }
    }

    private function deleteExistingTasks(PurchaseOrder $po)
    {
        $qb = $this->createQueryBuilder('task');
        $qb->delete()
            ->where('task.purchaseOrder = :po')
            ->setParameter('po', $po);
        $query = $qb->getQuery();
        $query->execute();
    }

    /** @return ProductionTask[] */
    public function findByPurchaseOrder(PurchaseOrder $po)
    {
        return $this->findBy(['purchaseOrder' => $po]);
    }
}
