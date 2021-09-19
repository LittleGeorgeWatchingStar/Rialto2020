<?php

namespace Rialto\Accounting\Transaction;


use Gumstix\Doctrine\HighLevelQueryBuilder;
use Rialto\Accounting\AccountingEvent;

class TransactionQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(TransactionRepo $repo)
    {
        parent::__construct($repo, 't');
        $this->qb->leftJoin('t.entries', 'entry');
    }

    public function bySystemType($sysType)
    {
        $this->qb->andWhere('t.systemType = :sysType')
            ->setParameter('sysType', $sysType);
        return $this;
    }

    public function byGroupNo($groupNo)
    {
        $this->qb->andWhere('t.groupNo = :groupNo')
            ->setParameter('groupNo', $groupNo);

        return $this;
    }

    public function byAccountingEvent(AccountingEvent $event)
    {
        return $this->bySystemType($event->getSystemType())
            ->byGroupNo($event->getSystemTypeNumber());
    }

    public function byAccount($glAccount)
    {
        $this->qb->andWhere('entry.account = :glAccount')
            ->setParameter('glAccount', $glAccount);
        return $this;
    }

    public function byStartDate(\DateTime $date)
    {
        $this->qb->andWhere('t.date >= :startDate')
            ->setParameter('startDate', $date);
        return $this;
    }

    public function byEndDate(\DateTime $date)
    {
        $this->qb->andWhere('t.date <= :endDate')
            ->setParameter('endDate', $date);
        return $this;
    }

    public function byMemoLike($memo)
    {
        $this->qb->andWhere('t.memo like :memoPattern')
            ->setParameter('memoPattern', "%$memo%");
        return $this;
    }

    public function sortByDate()
    {
        $this->qb->orderBy('t.date', 'asc');
        return $this;
    }
}
