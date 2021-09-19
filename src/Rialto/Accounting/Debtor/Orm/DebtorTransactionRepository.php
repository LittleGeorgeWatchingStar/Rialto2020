<?php

namespace Rialto\Accounting\Debtor\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\AccountingEventRepository;
use Rialto\Accounting\Debtor\DebtorCredit;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Rialto\Accounting\PaymentTransaction\PaymentTransactionRepository;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Sales\Customer\Customer;

class DebtorTransactionRepository
extends FilteringRepositoryAbstract
implements AccountingEventRepository, PaymentTransactionRepository
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('dt');
        $builder->add('customer', function(QueryBuilder $qb, $value) {
            $qb->andWhere('dt.customer = :custId');
            $qb->setParameter('custId', $value);
        });

        $builder->add('salesOrder', function(QueryBuilder $qb, $orderNo) {
            $qb->leftJoin(DebtorInvoice::class, 'invoice', 'WITH',
                    'dt.id = invoice.id')
                ->leftJoin(DebtorCredit::class, 'credit', 'WITH',
                    'dt.id = credit.id')
                ->leftJoin('credit.orderAllocations', 'orderAlloc')
                ->andWhere('(invoice.salesOrder = :orderNo or orderAlloc.salesOrder = :orderNo)')
                ->setParameter('orderNo', $orderNo);
        });

        $builder->add('startDate', function(QueryBuilder $qb, $value) {
            $qb->andWhere('dt.date >= :startDate');
            $qb->setParameter('startDate', $value);
        });

        $totalDQL = $this->transactionTotal('dt');
        $builder->add('settled', function(QueryBuilder $qb, $value) use ($totalDQL) {
            if ('yes' == $value) {
                $qb->andWhere("abs(dt.amountAllocated) = abs($totalDQL)");
            } elseif ('no' == $value) {
                $qb->andWhere("abs(dt.amountAllocated) != abs($totalDQL)");
            }
        });

        /** "status" is deprecated -- use "settled" instead. */
        $builder->add('status', function(QueryBuilder $qb, $value) use ($totalDQL) {
            switch ( $value ) {
                case 'unallocated':
                    $qb->andWhere("abs(dt.amountAllocated) < abs($totalDQL)");
                    break;
                case 'allocated':
                    $qb->andWhere("abs(dt.amountAllocated) >= abs($totalDQL)");
                    break;
                case 'settled':
                    $qb->andWhere("abs(dt.amountAllocated) = abs($totalDQL)");
                    break;
            }
        });

        $builder->add('systemType', function(QueryBuilder $qb, $typeID) {
           $qb->andWhere('dt.systemType = :typeID')
                ->setParameter('typeID', $typeID);
        });

        $builder->add('systemTypeNumber', function(QueryBuilder $qb, $typeNo) {
            $numbers = explode(',', $typeNo);
            $qb->andWhere('dt.systemTypeNumber in (:sysTypeNos)')
                ->setParameter('sysTypeNos', $numbers);
        });

        $builder->add('_order', function(QueryBuilder $qb, $orderBy) {
            switch($orderBy) {
            default:
                $qb->orderBy('dt.date', 'desc');
                break;
            }
        });

        return $builder->buildQuery($params);
    }

    /**
     *
     * @param Transaction $transaction
     * @return DebtorInvoice[]
     */
    public function findDebtorInvoicesForTransaction(Transaction $transaction)
    {
        $transactions = $this->findByEvent($transaction);
        return array_values(array_filter($transactions,
            function (DebtorTransaction $t) {
                return $t instanceof DebtorInvoice;
            }));
    }

    /**
     * @todo Should return unique result.
     * @param SystemType $type
     * @param int $typeNo
     * @return DebtorTransaction[]
     */
    public function findByType(SystemType $type, $typeNo)
    {
        return $this->findBy([
            'systemType' => $type->getId(),
            'systemTypeNumber' => $typeNo,
        ]);
    }

    /**
     * @todo findByType() should do this.
     * @param SystemType $type
     * @param int $typeNo
     * @return DebtorTransaction
     */
    public function findOneByType(SystemType $type, $typeNo)
    {
        return $this->findOneBy([
            'systemType' => $type->getId(),
            'systemTypeNumber' => $typeNo,
        ]);
    }

    /** @return DebtorTransaction[] */
    public function findByEvent(AccountingEvent $event)
    {
        return $this->findByType(
            $event->getSystemType(),
            $event->getSystemTypeNumber()
        );
    }

    public function findForAllocationMatching(
        \DateTime $since,
        Customer $customer = null,
        $limit = 500)
    {
        $totalAmount = $this->transactionTotal('dt');
        $qb = $this->createQueryBuilder('dt');
        $qb->andWhere('dt.date >= :since')
            ->andWhere("abs(dt.amountAllocated) < abs($totalAmount)")
            ->setParameter('since', $since)
            ->setMaxResults($limit);
        if ( $customer ) {
            $qb->andWhere('dt.customer = :customer')
                ->setParameter('customer', $customer->getId());
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $alias
     * @return string The DQL query fragment that calculates the transaction
     *   total.
     */
    private function transactionTotal($alias)
    {
        return "$alias.subtotalAmount + $alias.taxAmount + " .
            "$alias.shippingAmount + $alias.discountAmount";
    }

    /** @return QueryBuilder */
    public function queryEligibleCreditsToMatch(PaymentTransaction $invoice)
    {
        if (! $invoice instanceof DebtorTransaction ) {
            throw new \InvalidArgumentException("Wrong class");
        }
        if (! $invoice->isInvoice() ) {
            throw new \InvalidArgumentException("Argument 'invoice' must be an invoice");
        }

        $total = $this->transactionTotal('credit');
        $qb = $this->_em->createQueryBuilder();
        $qb->select('credit')
            ->from(DebtorCredit::class, 'credit')
            ->andWhere('credit.customer = :customer')
            ->setParameter('customer', $invoice->getCustomer())
            ->andWhere("credit.amountAllocated != $total")
            ->leftJoin('credit.allocations', 'alloc', 'WITH',
                'alloc.invoice = :invoice')
            ->andWhere('alloc.id is null')
            ->setParameter('invoice', $invoice)
        ;
        return $qb;
    }
}
