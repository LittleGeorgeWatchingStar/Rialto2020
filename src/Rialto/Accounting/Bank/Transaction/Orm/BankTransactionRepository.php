<?php

namespace Rialto\Accounting\Bank\Transaction\Orm;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Statement\BankStatementMatch;
use Rialto\Accounting\Bank\Statement\BankStatementPattern;
use Rialto\Accounting\Bank\Statement\Match\BankTransactionStrategy;
use Rialto\Accounting\Bank\Statement\Match\ChequeStrategy;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\FilteringRepositoryAbstract;

class BankTransactionRepository extends FilteringRepositoryAbstract
{
    public function findByFilters(array $params)
    {
        $query = $this->queryByFilters($params);
        return $query->getResult();
    }

    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('bt');
        $context = $this;
        $builder->add('cleared', function(QueryBuilder $qb, $value) use ($context) {
            if ('yes' == $value) {
                $context->selectCleared($qb);
            } elseif ('no' == $value) {
                $context->selectOutstanding($qb);
            }
        });
        $builder->add('type', function(QueryBuilder $qb, $value) {
            $qb->andWhere('bt.bankTransType = :btType');
            $qb->setParameter('btType', $value);
        });
        $builder->add('systemType', function(QueryBuilder $qb, $sysType) {
            $qb->andWhere('bt.systemType = :sysType')
                ->setParameter('sysType', $sysType);
        });
        $builder->add('date', function(QueryBuilder $qb, $date) {
            $qb->andWhere('date_diff(bt.date, :date) = 0')
                ->setParameter('date', $date);
        });
        $builder->add('startDate', function (QueryBuilder $qb, $date) {
            $qb->andWhere('bt.date >= :startDate')
                ->setParameter('startDate', $date);
        });
        $builder->add('endDate', function (QueryBuilder $qb, $date) {
            $qb->andWhere('bt.date <= :endDate')
                ->setParameter('endDate', $date);
        });
        $builder->add('bankAccount', function (QueryBuilder $qb, $account) {
            $qb->andWhere('bt.bankAccount = :bankAccount')
                ->setParameter('bankAccount', $account);
        });
        $builder->add('supplier', function(QueryBuilder $qb, $supplierId) {
            $qb->join(SupplierTransaction::class, 'st', 'WITH',
                    'bt.systemType = st.systemType and bt.systemTypeNumber = st.systemTypeNumber')
                ->andWhere('st.supplier = :supplierId')
                ->setParameter('supplierId', $supplierId);
        });
        $builder->add('memo', function (QueryBuilder $qb, $memo) {
            $qb->andWhere('bt.reference like :memo')
                ->setParameter('memo', "%$memo%");
        });
        $query = $builder->buildQuery($params);
        return $query;
    }

    private function selectCleared(QueryBuilder $qb)
    {
        $qb->innerJoin('bt.matches', 'match')
           ->having('bt.amount = sum(match.amountCleared)')
           ->groupBy('bt.id');
    }

    private function selectOutstanding(QueryBuilder $qb)
    {
        $bt = $qb->getRootAliases()[0];
        $qb->leftJoin("$bt.matches", 'match')
           ->addSelect('match')
           ->having($qb->expr()->orX(
               $qb->expr()->lt('sum(abs(match.amountCleared))', "abs($bt.amount)"),
               $qb->expr()->eq('count(match.amountCleared)', 0)
            ))
            ->groupBy("$bt.id");
    }

    public function findByBankStatement(BankStatement $st)
    {
        $qb = $this->createQueryBuilder('bt')
            ->innerJoin('bt.matches', 'match')
            ->where('match.bankStatement = :statement')
            ->setParameter('statement', $st->getId());
        return $qb->getQuery()->getResult();
    }

    /**
     * Bank transactions that match $pattern and $strategy.
     *
     * @return BankTransaction[]
     */
    public function findMatchingTransactions(
        BankStatementPattern $pattern,
        BankTransactionStrategy $strategy)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(
            BankTransaction::class, 'bt'
        );
        $rsm->addJoinedEntityFromClassMetadata(
            BankStatementMatch::class, 'match', 'bt', 'matches');
        $rsm->addScalarResult('amtDiff', 'amtDiff');

        $qb = $this->_em->getConnection()->createQueryBuilder();
        $qb->select('bt.*',
                'mat.*',
                'abs((bt.Amount - ifnull(sum(mat.amountCleared), 0)) - :amount) as amtDiff')
            ->from('BankTrans', 'bt')
            ->leftJoin('bt', 'BankStatementMatch', 'mat', 'bt.BankTransID = mat.transactionID')
        ;
        $params = [
            'amount' => $strategy->getTotalOutstanding(),
        ];

        if ( $pattern->getReferencePattern() ) {
            $qb->andWhere('bt.Ref regexp :reference');
            $params['reference'] = $pattern->getReferencePattern();
        }
        if ( $pattern->hasDateConstraint() ) {
            $qb->andWhere('abs(datediff(bt.TransDate, :date)) <= :dateTol');
            $params['date'] = $strategy->getDate();
            $params['dateTol'] = $pattern->getDateConstraint();
        }
        $qb->groupBy('bt.BankTransID')
            ->having('(sum(abs(mat.amountCleared))<abs(bt.Amount) or count(mat.amountCleared)=0)');
        if ( $pattern->hasAmountConstraint() ) {
            $qb->andHaving('amtDiff <= :amtTol');
            $params['amtTol'] = $pattern->getAmountConstraint();
        }
        $qb->orderBy('bt.TransDate', 'ASC');
        $qb->addOrderBy('amtDiff', 'ASC');

        $query = $this->_em->createNativeQuery($qb->getSQL(), $rsm);
        $query->setParameters($params);

        $results = $query->getResult();
        return $this->mixedToPure($results);
    }

    /**
     * Cheque bank transactions that match $pattern and $strategy.
     *
     * @return BankTransaction[]
     */
    public function findMatchingCheques(
        BankStatementPattern $pattern,
        ChequeStrategy $strategy)
    {
        $chequeNo = $strategy->getChequeNumber();
        if ( $chequeNo ) {
            $matching = $this->findMatchingChequesByChequeNumber($pattern, $strategy);
            if ( count($matching) > 0 ) {
                return $matching;
            }
        }
        return $this->findMatchingChequesByAmountAndDate($pattern, $strategy);
    }

    private function findMatchingChequesByChequeNumber(
        BankStatementPattern $pattern,
        ChequeStrategy $strategy)
    {
        $qb = $this->createQueryBuilder('bt');
        $this->selectOutstanding($qb);

        $qb->where('bt.chequeNumber = :chequeNo');
        $params = [
            'chequeNo' => $strategy->getChequeNumber(),
        ];
        if ( $pattern->hasDateConstraint() ) {
            $qb->andWhere('date_diff(:date, bt.date) between 0 and :dateTol');
            $params['dateTol'] = $pattern->getDateConstraint();
            $params['date'] = $strategy->getDate();
        }
        $qb->setParameters($params);
        return $qb->getQuery()->getResult();
    }

    private function findMatchingChequesByAmountAndDate(
        BankStatementPattern $pattern,
        ChequeStrategy $strategy)
    {
        $qb = $this->createQueryBuilder('bt');
        $amtOutstanding = '(bt.amount - ifnull(sum(match.amountCleared), 0))';
        $this->selectOutstanding($qb);
        $qb->select('bt')
            ->addSelect("abs($amtOutstanding - :amount) as HIDDEN amtDiff")
            ->addSelect("date_diff(:date, bt.date) as HIDDEN dateDiff")
            ->where('bt.bankTransType = :type')
            ->orderBy('amtDiff', 'ASC')
            ->addOrderBy('dateDiff', 'ASC')
            ->setParameters([
                'type' => BankTransaction::TYPE_CHEQUE,
                'date' => $strategy->getDate(),
                'amount' => $strategy->getTotalOutstanding(),
            ]);
        if ( $pattern->hasAmountConstraint() ) {
            $qb->andWhere('abs(bt.amount - :amount) < :amtTol')
                ->setParameter('amtTol', $pattern->getAmountConstraint());
        }
        if ( $pattern->hasDateConstraint() ) {
            $qb->andWhere('date_diff(:date, bt.date) between 0 and :dateTol')
                ->setParameter('dateTol', $pattern->getDateConstraint());
        }

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findByEvent(AccountingEvent $event)
    {
        $qb = $this->createQueryBuilder('bt');
        $qb->where('bt.systemType = :sysType')
            ->andWhere('bt.systemTypeNumber = :typeNo')
            ->setParameters([
                'sysType' => $event->getSystemType()->getId(),
                'typeNo' => $event->getSystemTypeNumber()
            ]);
        return $qb->getQuery()->getResult();
    }

    /** @return BankTransaction[] */
    public function findChequesToPrint(
        BankAccount $account,
        SystemType $sysType,
        $chequeNos = [])
    {
        $qb = $this->createQueryBuilder('bt');
        $qb->andWhere('bt.bankTransType = :cheque')
            ->setParameter('cheque', BankTransaction::TYPE_CHEQUE)
            ->andWhere('bt.bankAccount = :account')
            ->setParameter('account', $account)
            ->andWhere('bt.systemType = :sysType')
            ->setParameter('sysType', $sysType);
        if ( count($chequeNos) > 0 ) {
            $qb->andWhere('bt.chequeNumber in (:chequeNos)')
                ->setParameter('chequeNos', $chequeNos);
        } else {
            $qb->andWhere('bt.printed = 0')
                ->andWhere('bt.chequeNumber > 0');
        }
        return $qb->getQuery()->getResult();
    }
}
