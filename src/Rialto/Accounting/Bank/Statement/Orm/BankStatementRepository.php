<?php

namespace Rialto\Accounting\Bank\Statement\Orm;

use DateTime;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Statement\BankStatementMatch;
use Rialto\Accounting\Bank\Statement\BankStatementPattern;
use Rialto\Accounting\Bank\Statement\Match\BankStatementMatchRepository;
use Rialto\Accounting\Bank\Statement\Match\MatchStrategy;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Period\Period;
use Rialto\Database\Orm\RialtoRepositoryAbstract;

class BankStatementRepository extends RialtoRepositoryAbstract
{
    /** @return BankStatement */
    public function findOrCreate(
        BankAccount $bankAccount,
        DateTime $date,
        $amount,
        $bankRef,
        $custRef,
        $description)
    {
        $statement = $this->findOneBy([
            'bankAccount' => $bankAccount,
            'date' => $date,
            'amount' => $amount,
            'bankReference' => $bankRef,
            'customerReference' => $custRef,
        ]);
        if (! $statement ) {
            $statement = new BankStatement($bankAccount,
                $date, $amount, $bankRef, $custRef, $description);
        }
        return $statement;
    }

    public function findOutstandingSince(DateTime $since, $orderBy = null, ?BankAccount $bankAccount = null)
    {
        $qb = $this->createQueryBuilder('statement')
            ->leftJoin('statement.matches', 'match')
            ->where('statement.date >= :since')
            ->having('abs(statement.amount) > sum(abs(match.amountCleared))')
            ->orHaving('count(match.amountCleared) = 0')
            ->groupBy('statement.id')
            ->setParameter('since', $since);

        if ($bankAccount) {
            $qb->andWhere('statement.bankAccount = :bankAccount')
                ->setParameter('bankAccount', $bankAccount);
        }

        switch ($orderBy) {
            case 'description':
            case 'amount':
                $qb->orderBy("statement.$orderBy");
                break;
            case 'oldest first':
                $qb->orderBy('statement.date', 'asc');
                break;
            default:
                $qb->orderBy('statement.date', 'desc')
                    ->addOrderBy('statement.amount');
                break;
        }
        return $qb->getQuery()->getResult();
    }

    public function findAdditionalStatements(
        MatchStrategy $strategy,
        BankStatementPattern $pattern)
    {
        $sql = "select s.*
            from BankStatements s
            left join BankStatementMatch m
            on m.statementID = s.BankStatementID
            where m.transactionID is null
            and s.BankStatementID != :origId
            and s.BankDescription regexp :pattern";

        $originalStatement = $strategy->getStatement();
        $params = [
            'origId' => $originalStatement->getId(),
            'pattern' => $pattern->getAdditionalStatementPattern(),
        ];
        if ( $pattern->hasAdditionalStatementDateConstraint() ) {
            $sql .= " and abs(datediff(s.BankPostDate, :date)) <= :dateTol";
            $params['date'] =  $strategy->getDate();
            $params['dateTol'] = $pattern->getAdditionalStatementDateConstraint();
        }
        $sql .= " order by left(s.BankDescription, 4), BankPostDate asc";

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(BankStatement::class, 's');
        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameters($params);
        logDebug($query->getParameters(), $query->getSQL());
        return $query->getResult();
    }

    public function link(BankStatement $st, BankTransaction $tr, $amount)
    {
        /** @var $repository BankStatementMatchRepository*/
        $repository = $this->_em->getRepository(BankStatementMatch::class);
        $match = $repository->findOrCreate($st, $tr);
        $match->setAmountCleared($amount);
        $this->_em->persist($match);
        return $match;
    }

    /** @return BankStatement[] */
    public function findByPeriod(Period $period, ?BankAccount $account = null)
    {
        $qb = $this->createQueryBuilder('st');
        $qb->select('st')
            ->addSelect('SUBSTRING(st.description, 1, 4) as HIDDEN grouping')
            ->where('st.date >= :start')
            ->andWhere('st.date <= :end')
            ->orderBy('grouping', 'asc')
            ->addOrderBy('st.date', 'asc')
            ->setParameters([
                'start' => $period->getStartDate(),
                'end' => $period->getEndDate(),
            ]);
        if ($account) {
            $qb->andWhere('st.bankAccount = :bankAccount')
                ->setParameter('bankAccount', $account);
        }
        $query = $qb->getQuery();
        return $query->getResult();
    }
}
