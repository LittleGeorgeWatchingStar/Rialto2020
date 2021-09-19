<?php

namespace Rialto\Stock\Returns;


use DateTime;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Stock\Facility\Facility;

class ReturnedItemRepository extends RialtoRepositoryAbstract
{
    /** @return ReturnedItem[] */
    public function findByDate(DateTime $received)
    {
        $min = clone $received;
        $min->modify('-5 minutes');
        $max = clone $received;
        $max->modify('+5 minutes');

        $qb = $this->createQueryBuilder('item');
        $qb->andWhere('item.dateCreated between :min and :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max);
        return $qb->getQuery()->getResult();
    }

    /**
     * @return ReturnedItem[]
     */
    public function findResolvedByLocations(Facility $returnedFrom, Facility $returnedTo)
    {
        $qb = $this->createQueryBuilder('item');
        $qb->andWhere('item.returnedFrom = :from')
            ->setParameter('from', $returnedFrom)
            ->andWhere('item.returnedTo = :to')
            ->setParameter('to', $returnedTo);
        $items = $qb->getQuery()->getResult();
        return array_filter($items, function(ReturnedItem $i) {
            return ! $i->hasProblems();
        });
    }
}
