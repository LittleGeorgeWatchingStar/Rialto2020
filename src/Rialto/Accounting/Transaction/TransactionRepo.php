<?php

namespace Rialto\Accounting\Transaction;


use Doctrine\ORM\Query;
use Gumstix\Doctrine\HighLevelFilter;
use Rialto\Database\Orm\FilteringRepository;
use Rialto\Database\Orm\RialtoRepositoryAbstract;

class TransactionRepo extends RialtoRepositoryAbstract implements FilteringRepository
{
    /** @return Query */
    public function queryByFilters(array $params)
    {
        $filters = new HighLevelFilter($this->createBuilder());

        $filters->add('sysType', function(TransactionQueryBuilder $qb, $sysType) {
            $qb->bySystemType($sysType);
        });

        $filters->add('groupNo', function(TransactionQueryBuilder $qb, $groupNo) {
            $qb->byGroupNo($groupNo);
        });

        $filters->add('account', function (TransactionQueryBuilder $qb, $account) {
            $qb->byAccount($account);
        });

        $filters->add('startDate', function (TransactionQueryBuilder $qb, $startDate) {
            $qb->byStartDate($startDate);
        });

        $filters->add('endDate', function (TransactionQueryBuilder $qb, $endDate) {
            $qb->byEndDate($endDate);
        });

        $filters->add('memo', function (TransactionQueryBuilder $qb, $memo) {
            $qb->byMemoLike($memo);
        });

        return $filters->buildQuery($params);
    }

    /** @return TransactionQueryBuilder */
    public function createBuilder()
    {
        return new TransactionQueryBuilder($this);
    }
}
