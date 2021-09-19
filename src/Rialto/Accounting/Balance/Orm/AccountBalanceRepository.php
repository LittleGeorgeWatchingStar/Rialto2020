<?php

namespace Rialto\Accounting\Balance\Orm;

use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\Balance\AccountBalance;
use Rialto\Accounting\Period\Period;
use Rialto\Database\Orm\FilteringRepositoryAbstract;

class AccountBalanceRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('balance');
        $builder->add('account', function(QueryBuilder $qb, $account) {
            $qb->andWhere('balance.account = :account')
                ->setParameter('account', $account);
        });

        $builder->add('fromPeriod', function(QueryBuilder $qb, $period) {
            $qb->andWhere('balance.period >= :fromPeriod')
                ->setParameter('fromPeriod', $period);
        });

        $builder->add('toPeriod', function(QueryBuilder $qb, $period) {
            $qb->andWhere('balance.period <= :toPeriod')
                ->setParameter('toPeriod', $period);
        });

        return $builder->buildQuery($params);
    }


    /** @return AccountBalance[] */
    public function findAllSincePeriod(Period $period)
    {
        $qb = $this->createQueryBuilder('bal');
        $qb->join('bal.period', 'period')
            ->where('period.id >= :periodNo')
            ->setParameter('periodNo', $period->getId())
            ->orderBy('period.id', 'asc');
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds the account balance record whose account is the same as $current
     * and whose period is the one immediately following that of $current.
     *
     * @return AccountBalance|null
     */
    public function findNext(AccountBalance $current)
    {
        return $this->findOneBy([
            'account' => $current->getAccountCode(),
            'period' => $current->getPeriodNo() + 1,
        ]);
    }

    /**
     * Updates the balance for any GL account that has unposted GL entries.
     *
     * @param DateTime|null $closedCutoff Set to UNIX epoch if null.
     * @return int The number of GLEntries posted.
     * @throws DBALException
     * @todo mantis4482: don't change closed periods driven by data instead of param.
     */
    public function postUnpostedEntries($closedCutoff = null): int
    {
        if (!$closedCutoff) {
            $closedCutoff = new DateTime('@0');
        }

        $conn = $this->_em->getConnection();

        $periodTotals = "SELECT sum(Amount) as TotalAmount,
            Account,
            PeriodNo AS Period
            FROM GLTrans
            WHERE Posted = 0
            AND TranDate >= :closedCutoff
            GROUP BY Account, Period";

        $updateActuals = "UPDATE ChartDetails balance
            JOIN ($periodTotals) AS total
                ON balance.AccountCode = total.Account
                AND balance.Period = total.Period
            SET balance.Actual = balance.Actual + total.TotalAmount";
        $conn->executeUpdate($updateActuals, [
            'closedCutoff' => $closedCutoff->format('Y-m-d H:i:s'),
        ]);

        $stmt = $conn->executeQuery($periodTotals, [
            'closedCutoff' => $closedCutoff->format('Y-m-d H:i:s'),
        ]);
        foreach ( $stmt->fetchAll() as $total ) {
            $updateBalanceFwd = "UPDATE ChartDetails
                SET BFwd = BFwd + :amount
                WHERE AccountCode = :account
                AND Period > :period";
            $params = [
                'amount' => $total['TotalAmount'],
                'account' => $total['Account'],
                'period' => $total['Period'],
            ];
            $conn->executeUpdate($updateBalanceFwd, $params);
        }

        $setPosted = "UPDATE GLTrans
            SET Posted = 1
            WHERE Posted = 0
            AND TranDate >= :closedCutoff";
        $affected = $conn->executeUpdate($setPosted, [
            'closedCutoff' => $closedCutoff->format('Y-m-d H:i:s'),
        ]);

        /* Force updated entities to be reloaded from the DB. */
        $this->_em->clear();

        return $affected;
    }

    /**
     * Recalculates all account balances starting from $period.
     *
     * @return AccountBalance[]
     *  The updated balances.
     */
    public function repostBalancesFromPeriod(Period $period)
    {
        $conn = $this->_em->getConnection();
        $params = ['period' => $period->getId()];

        /* Make the posted flag on all GL entries including and after the period selected = 0 */
        $clearPosted = "UPDATE GLTrans
            SET Posted = 0
            WHERE PeriodNo >= :period";
        $conn->executeUpdate($clearPosted, $params);

        /* Now make all the actuals 0 for all periods including and after the period from */
        $clearActual = "UPDATE ChartDetails
            SET Actual = 0
            WHERE Period >= :period";
        $conn->executeUpdate($clearActual, $params);

        $this->postUnpostedEntries();

        /* Make a note of all the subsequent periods to recalculate the B/Fwd balances for */
        $balances = $this->findAllSincePeriod($period);

        foreach ( $balances as $current )
        {
            $next = $this->findNext($current);
            if ( null === $next ) continue;

            $nextBalanceFwd = $current->getBalanceFwd() + $current->getActual();
            $next->setBalanceFwd($nextBalanceFwd);

            $nextBudgetFwd = $current->getBudgetFwd() + $current->getBudget();
            $next->setBudgetFwd($nextBudgetFwd);
        }
        $this->_em->flush();

        return $balances;
    }

    /**
     * Fetches the AccountBalance records for the given periods, sorted
     * as required by the balance sheet.
     *
     * @param Period[] $periods
     * @return AccountBalance[]
     */
    public function findForBalanceSheet(array $periods)
    {
        $qb = $this->queryByPeriods($periods);
        $qb->andWhere('gr.profitAndLoss = 0');
        return $qb->getQuery()->getResult();
    }

    private function queryByPeriods(array $periods)
    {
        $qb = $this->createQueryBuilder('bal');
        $qb->join('bal.account', 'account')
            ->join('account.accountGroup', 'gr')
            ->where('bal.period in (:periods)')
            ->setParameter('periods', $periods)
            ->orderBy('gr.section')
            ->addOrderBy('gr.sequenceInTB')
            ->addOrderBy('account.id');
        return $qb;
    }

    /** @return float */
    public function findProfitAndLossTotalForBalanceSheet(Period $period)
    {
        $qb = $this->createQueryBuilder('bal');
        $qb->select('sum(section.sign * (bal.actual + bal.balanceFwd))')
            ->join('bal.account', 'account')
            ->join('account.accountGroup', 'gr')
            ->join('gr.section', 'section')
            ->where('bal.period = :period')
            ->setParameter('period', $period->getId())
            ->andWhere('gr.profitAndLoss = 1');
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Fetches the AccountBalance records for the given periods, sorted
     * as required by the profit and loss statement.
     *
     * @param Period[] $periods
     * @return string[][]
     */
    public function findForProfitAndLoss(Period $start, Period $end)
    {
        $sql = "
            SELECT section.id as sectionID
                , section.name as sectionName
                , g.GroupName as groupName
			    , account.AccountCode as accountID
			    , account.AccountName as accountName
			    , section.sign * (endBalance.BFwd + endBalance.Actual - startBalance.BFwd) as amount
            FROM ChartMaster as account
            JOIN AccountGroups as g
                ON account.Group_ = g.GroupName
            JOIN ChartDetails as balance
                ON account.AccountCode = balance.AccountCode
            JOIN Accounting_Section as section
                ON g.SectionInAccounts = section.id
            JOIN (
                select * from ChartDetails where Period = :startPeriod
            ) as startBalance on startBalance.AccountCode = account.AccountCode
            JOIN (
                select * from ChartDetails where Period = :endPeriod
            ) as endBalance on endBalance.AccountCode = account.AccountCode
            WHERE g.PandL = 1
            GROUP BY balance.AccountCode
            ORDER BY section.id, g.SequenceInTB, account.AccountCode
        ";

        $conn = $this->_em->getConnection();
        return $conn->fetchAll($sql, [
            'startPeriod' => $start->getId(),
            'endPeriod' => $end->getId(),
        ]);
    }
}
