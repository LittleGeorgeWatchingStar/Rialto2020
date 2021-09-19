<?php

namespace Rialto\Manufacturing\Requirement\Orm;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Stock\Item;

class RequirementRepository extends RialtoRepositoryAbstract
{
    /** @return Requirement[] */
    public function findByWorkOrder(WorkOrder $order)
    {
        return $this->findBy([
            'workOrder' => $order->getId()
        ]);
    }

    /** @return Requirement|object|null */
    public function findByWorkOrderAndItem(WorkOrder $order, Item $item)
    {
        return $this->findOneBy([
            'workOrder' => $order->getId(),
            'stockItem' => $item->getSku(),
        ]);
    }

    /**
     * Find competitors that are lower priority than $requirement.
     *
     * @return Requirement[]
     */
    public function findLowerPriorityCompetitors(RequirementCollection $requirements)
    {
        $qb = $this->queryCompetitors($requirements);

        $myDate = $this->getDateNeeded($requirements);
        if ( $myDate ) {
            $qb->andWhere('(wo.requestedDate is null or wo.requestedDate > :myDate)')
                ->setParameter('myDate', $myDate);
        } else {
            $qb->andWhere('wo.requestedDate is null');
        }
        return $qb->getQuery()->getResult();
    }

    /** @return DateTime|null */
    private function getDateNeeded(RequirementCollection $requirements)
    {
        $earliest = null;
        foreach ($requirements->getRequirements() as $requirement) {
            $date = $requirement->getConsumer()->getDueDate();
            if (null === $earliest) {
                $earliest = $date;
            } elseif ($date < $earliest) {
                $earliest = $date;
            }
        }
        return $earliest;
    }

    /**
     * Query to find other requirements needed at the same location that are
     * "competing" with $requirement for the same parts.
     *
     * @return QueryBuilder
     */
    private function queryCompetitors(RequirementCollection $requirements)
    {
        $myVersion = $requirements->getVersion();
        $myLocation = $requirements->getFacility();

        $qb = $this->createQueryBuilder('wor')
            ->join('wor.workOrder', 'wo')
            ->join('wo.purchaseOrder', 'po')
            ->join('wor.allocations', 'alloc')
            ->andWhere('po.buildLocation = :loc')
            ->setParameter('loc', $myLocation->getId())
            ->andWhere('wor not in (:thief)')
            ->setParameter('thief', $requirements->getRequirements())
            ->andWhere('wor.stockItem = :item')
            ->setParameter('item', $requirements->getSku())
            ->groupBy('wor.id')
            ->having('sum(alloc.qtyAllocated) > 0')
            ->orderBy('wo.requestedDate', 'DESC');

        if ( $myVersion->isSpecified() ) {
            $qb->andWhere('wor.version = :version')
                ->setParameter('version', (string)$myVersion);
        }
        return $qb;
    }

    /**
     * Find competitors that are higher priority than $requirement.
     *
     * @return Requirement[]
     */
    public function findHigherPriorityCompetitors(RequirementCollection $requirements)
    {
        $qb = $this->queryCompetitors($requirements);

        $myDate = $this->getDateNeeded($requirements);
        if ( $myDate ) {
            $qb->andWhere('wo.requestedDate < :myDate')
                ->setParameter('myDate', $myDate);
        } else {
            $qb->andWhere('wo.requestedDate is not null');
        }
        return $qb->getQuery()->getResult();
    }
}
