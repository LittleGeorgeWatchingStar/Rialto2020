<?php

namespace Rialto\Accounting\Ledger\Entry\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\FilteringRepositoryAbstract;

class GLEntryRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('entry');
        $builder->join('entry.period', 'period');

        $builder->add('account', function(QueryBuilder $qb, $accountID) {
            $qb->andWhere('entry.account = :accountID')
                ->setParameter('accountID', $accountID);
        });

        $builder->add('systemTypeNumber', function(QueryBuilder $qb, $typeNo) {
            $qb->andWhere('entry.systemTypeNumber = :sysTypeNo')
                ->setParameter('sysTypeNo', $typeNo);
        });

        $builder->add('startPeriod', function(QueryBuilder $qb, $periodNo) {
            $qb->andWhere('period.id >= :startPeriod')
                ->setParameter('startPeriod', $periodNo);
        });

        $builder->add('endPeriod', function(QueryBuilder $qb, $periodNo) {
            $qb->andWhere('period.id <= :endPeriod')
                ->setParameter('endPeriod', $periodNo);
        });

        $builder->add('startDate', function(QueryBuilder $qb, $date) {
            $qb->andWhere('entry.date >= :startDate')
                ->setParameter('startDate', $date);
        });

        $builder->add('endDate', function(QueryBuilder $qb, $date) {
            $qb->andWhere('entry.date <= :endDate')
                ->setParameter('endDate', $date);
        });

        $builder->add('narrative', function(QueryBuilder $qb, $pattern) {
            $qb->andWhere('entry.narrative like :pattern')
                ->setParameter('pattern', "%$pattern%");
        });

        $builder->add('minAmount', function(QueryBuilder $qb, $amt) {
            $qb->andWhere('abs(entry.amount) >= :minAmount')
                ->setParameter('minAmount', $amt);
        });

        $builder->add('maxAmount', function(QueryBuilder $qb, $amt) {
            $qb->andWhere('abs(entry.amount) <= :maxAmount')
                ->setParameter('maxAmount', $amt);
        });

        $builder->add('posted', function(QueryBuilder $qb, $posted) {
            if ($posted == 'yes') {
                $qb->andWhere('entry.posted = 1');
            } elseif ($posted == 'no') {
                $qb->andWhere('entry.posted = 0');
            }
        });

        $builder->add('_order', function(QueryBuilder $qb, $orderBy) {
            switch ($orderBy) {
            default:
                $qb->orderBy('entry.date')
                    ->addOrderBy('entry.id');
            }
        });

        return $builder->buildQuery($params);
    }

    /**
     * @param SystemType $type
     * @param int $typeNo
     * @return GLEntry[]
     */
    public function findByType(SystemType $type, $typeNo)
    {
        return $this->findBy([
            'systemType' => $type->getId(),
            'systemTypeNumber' => $typeNo
        ]);
    }

    public function findByEvent(AccountingEvent $event)
    {
        return $this->findByType(
            $event->getSystemType(),
            $event->getSystemTypeNumber()
        );
    }

    /** @return int The number of unposted GL entries */
    public function countUnposted()
    {
        $qb = $this->createQueryBuilder('entry');
        $qb->select('count(entry.id)')
            ->where('entry.posted = 0');
            // todo: mantis4482 - only count open periods?
        return $qb->getQuery()->getSingleScalarResult();
    }
}
