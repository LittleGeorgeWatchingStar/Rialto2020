<?php

namespace Rialto\Accounting\Card\Orm;

use Doctrine\ORM\QueryBuilder;
use Gumstix\Time\DateRange;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\AccountingEventRepository;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\FilteringRepositoryAbstract;

class CardTransactionRepository
extends FilteringRepositoryAbstract
implements AccountingEventRepository
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('ct');
        $builder->leftJoin(DebtorTransaction::class, 'dt', 'WITH',
            'ct.systemType = dt.systemType and ct.systemTypeNumber = dt.systemTypeNumber');

        $builder->add('customer', function(QueryBuilder $qb, $customerID) {
            $qb->andWhere('dt.customer = :customerID')
                ->setParameter('customerID', $customerID);
        });

        $builder->add('type', function(QueryBuilder $qb, $typeID) {
            $qb->andWhere('ct.systemType = :typeID')
                ->setParameter('typeID', $typeID);
        });

        $builder->add('postDate', function(QueryBuilder $qb, $date) {
            $qb->andWhere('date_diff(ct.postDate, :date) = 0')
                ->setParameter('date', $date);
        });

        return $builder->buildQuery($params);
    }

    /**
     * @todo Should return unique result.
     * @param SystemType $type
     * @param int $typeNo
     * @return CardTransaction[]
     */
    public function findByType(SystemType $type, $typeNo)
    {
        return $this->findBy([
            'systemType' => $type->getId(),
            'systemTypeNumber' => $typeNo,
        ]);
    }

    public function findByEvent(AccountingEvent $event)
    {
        $qb = $this->createQueryBuilder('ct');
        $qb->where('ct.systemType = :sysType')
            ->andWhere('ct.systemTypeNumber = :typeNo')
            ->setParameters([
                'sysType' => $event->getSystemType()->getId(),
                'typeNo' => $event->getSystemTypeNumber()
            ]);
        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all transactions with the given postDate that are sweepable,
     * even if they have already been swept.
     * @return CardTransaction[]
     */
    public function findSweepable(\DateTime $postDate)
    {
        $qb = $this->querySweepable();
        $qb->andWhere('date_diff(ct.postDate, :date) = 0')
            ->setParameter('date', $postDate->format('Y-m-d'));
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    private function querySweepable()
    {
        $qb = $this->createQueryBuilder('ct');
        $qb->andWhere('ct.void = 0')
            ->andWhere('ct.systemType in (:sysTypes)')
            ->setParameter('sysTypes', CardTransaction::getSweepableTypes())
            ->orderBy('ct.postDate');
        return $qb;
    }

    /**
     * All unswept transactions within the date range.
     * @return CardTransaction[]
     */
    public function findUnswept(DateRange $dates)
    {
        $qb = $this->querySweepable();
        $qb->andWhere('ct.settled = 0')
            ->andWhere('ct.postDate >= :start')
            ->setParameter('start', $dates->formatStart('Y-m-d'))
            ->andWhere('ct.postDate <= :end')
            ->setParameter('end', $dates->formatEnd('Y-m-d'));
        return $qb->getQuery()->getResult();
    }
}
