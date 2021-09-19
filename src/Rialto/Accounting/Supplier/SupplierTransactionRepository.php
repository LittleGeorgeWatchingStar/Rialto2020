<?php

namespace Rialto\Accounting\Supplier;

use DateTime;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Gumstix\Time\DateRange;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\AccountingEventRepository;
use Rialto\Accounting\Bank\Statement\BankStatementPattern;
use Rialto\Accounting\Bank\Statement\Match\ExistingSupplierInvoiceStrategy;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Rialto\Accounting\PaymentTransaction\PaymentTransactionRepository;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Recurring\RecurringInvoice;

class SupplierTransactionRepository
extends FilteringRepositoryAbstract
implements AccountingEventRepository, PaymentTransactionRepository
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('st');
        $builder->join('st.supplier', 'supplier');
        $builder->add('id', function(QueryBuilder $qb, $list) {
            if ( is_string($list) ) {
                $list = explode(',', $list);
            }
            $qb->andWhere('st.id in (:IDs)')
                ->setParameter('IDs', $list);
        });
        $builder->add('supplier', function(QueryBuilder $qb, $supplierID) {
            $qb->andWhere('st.supplier = :supplierID')
                ->setParameter('supplierID', $supplierID);
        });
        /* "since" is deprecated -- use "startDate" instead */
        $builder->add('since', function(QueryBuilder $qb, $value) {
            $qb->andWhere('st.date >= :startDate');
            $qb->setParameter('startDate', $value);
        });
        $builder->add('startDate', function(QueryBuilder $qb, $date) {
            $qb->andWhere('st.date >= :startDate');
            $qb->setParameter('startDate', $date);
        });
        $builder->add('endDate', function(QueryBuilder $qb, $date) {
            $date = new DateTime($date);
            $date->setTime(23, 59, 59);
            $qb->andWhere('st.date <= :endDate');
            $qb->setParameter('endDate', $date->format("Y-m-d H:i:s"));
        });
        $builder->add('dates', function (QueryBuilder $qb, DateRange $dates) {
            if ($dates->hasStart()) {
                $qb->andWhere('st.date >= :startDate');
                $qb->setParameter('startDate', $dates->getStart());
            }
            if ($dates->hasEnd()) {
                $qb->andWhere('DATE(st.date) <= :endDate');
                $qb->setParameter('endDate', $dates->getEnd());
            }
        });
        $builder->add('reference', function(QueryBuilder $qb, $reference) {
            $qb->andWhere('st.reference like :ref')
                ->setParameter('ref', "%$reference%");
        });
        $builder->add('type', function(QueryBuilder $qb, $sysTypeID) {
            $qb->andWhere('st.systemType = :sysType')
                ->setParameter('sysType', $sysTypeID);
        });
        $builder->add('systemTypeNumber', function(QueryBuilder $qb, $typeNo) {
            $numbers = explode(',', $typeNo);
            $qb->andWhere('st.systemTypeNumber in (:systemTypeNumber)')
                ->setParameter('systemTypeNumber', $numbers);
        });
        $builder->add('settled', function(QueryBuilder $qb, $settled) {
            if ( 'yes' == $settled ) {
                $qb->andWhere('st.settled = 1');
            }
            elseif ( 'no' == $settled ) {
                $qb->andWhere('st.settled = 0');
            }
        });
        $builder->add('credit', function(QueryBuilder $qb, $isCredit) {
            if ( 'yes' == $isCredit ) {
                $qb->andWhere('st.systemType in (:creditTypes)')
                    ->setParameter('creditTypes', SupplierTransaction::getCreditTypes());
            } elseif ('no' == $isCredit) {
                $qb->andWhere('st.systemType not in (:creditTypes)')
                    ->setParameter('creditTypes', SupplierTransaction::getCreditTypes());
            }
        });

        $builder->add('minAmount', function(QueryBuilder $qb, $amount) {
            $qb->andWhere('abs(st.subtotalAmount) >= :minAmount')
                ->setParameter('minAmount', $amount);
        });
        $builder->add('maxAmount', function(QueryBuilder $qb, $amount) {
            $qb->andWhere('abs(st.subtotalAmount + st.taxAmount) <= :maxAmount')
                ->setParameter('maxAmount', $amount);
        });

        $builder->add('_order', function(QueryBuilder $qb, $orderBy) {
            switch ( $orderBy ) {
            case 'supplier':
                $qb->orderBy('supplier.name')
                    ->addOrderBy('st.date');
                break;
            default:
                $qb->orderBy('st.date');
                break;
            }
        });

        return $builder->buildQuery($params);
    }


    /** @return SupplierTransaction|object */
    public function findSupplierPaymentByCheque(BankTransaction $cheque)
    {
        return $this->findOneBy([
            'systemTypeNumber' => $cheque->getSystemTypeNumber(),
            'systemType' => SystemType::CREDITOR_PAYMENT
        ]);
    }

    /**
     * @return SupplierTransaction[]
     */
    public function findByEvent(AccountingEvent $event)
    {
        return $this->findByType($event->getSystemType(), $event->getSystemTypeNumber());
    }

    /**
     * @todo Should return unique result.
     * @return SupplierTransaction[]
     */
    public function findByType(SystemType $sysType, $typeNo)
    {
        return $this->findBy([
            'systemType' => $sysType->getId(),
            'systemTypeNumber' => $typeNo,
        ]);
    }

    public function findMatchingInvoices(
        BankStatementPattern $pattern,
        ExistingSupplierInvoiceStrategy $strategy)
    {
        $supplier = $pattern->getSupplier();
        assertion(null != $supplier);
        $sql = "select tran.* from SuppTrans as tran
            join Accounting_Transaction glTrans
                on tran.transactionId = glTrans.id
            where tran.SupplierNo = :supplier
            and tran.SuppReference regexp :suppRef
            and glTrans.sysType = :sysType";
        $params = [
            'supplier' => $supplier->getId(),
            'suppRef' => $pattern->getReferencePattern(),
            'sysType' => SystemType::PURCHASE_INVOICE,
        ];
        if ( $pattern->hasDateConstraint() ) {
            $sql .= " and abs(datediff(tran.TranDate, :date)) <= :dateTol";
            $params['date'] = $strategy->getDate();
            $params['dateTol'] = $pattern->getDateConstraint();
        }

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(
            SupplierTransaction::class, 'tran'
        );

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameters($params);
        return $query->getResult();
    }

    /**
     * @return SupplierTransaction[]
     */
    public function findByInvoice(SupplierInvoice $inv)
    {
        return $this->findBy([
            'supplier' => $inv->getSupplier(),
            'reference' => $inv->getSupplierReference(),
        ]);
    }

    /** @return QueryBuilder */
    public function queryEligibleCreditsToMatch(PaymentTransaction $invoice)
    {
        if (! $invoice instanceof SupplierTransaction ) {
            throw new \InvalidArgumentException("Wrong class");
        }
        if (! $invoice->isInvoice() ) {
            throw new \InvalidArgumentException("Argument 'invoice' must be an invoice");
        }

        $totalAmount = $this->transactionTotal('credit');
        $qb = $this->createQueryBuilder('credit');
        $qb->andWhere('credit.systemType in (:creditTypes)')
            ->setParameter('creditTypes', SupplierTransaction::getCreditTypes())

            ->andWhere('credit.supplier = :supplier')
            ->setParameter('supplier', $invoice->getSupplier())

            ->andWhere("credit.amountAllocated != $totalAmount")
            ->leftJoin('credit.creditAllocations', 'alloc', "WITH",
                "alloc.invoice = :invoice")
            ->setParameter('invoice', $invoice)
            ->andWhere('alloc.id is null')

            ->addSelect('abs(date_diff(credit.date, :invoiceDate)) as HIDDEN sortVal')
            ->setParameter('invoiceDate', $invoice->getDate())
            ->orderBy('sortVal');

        return $qb;
    }

    /**
     * @return string The DQL query fragment that calculates the transaction
     *   total.
     */
    private function transactionTotal(string $alias): string
    {
        return "$alias.subtotalAmount + $alias.taxAmount";
    }

    /**
     * Finds the transaction created by the recurring invoice template
     * for the given date, if such a transaction exists.
     *
     * @return SupplierTransaction|null
     */
    public function findByRecurringInvoiceAndDate(
        RecurringInvoice $rInvoice,
        DateTime $date)
    {
        $qb = $this->createQueryBuilder('si');
        $qb->where('si.recurringInvoice = :riID')
            ->setParameter('riID', $rInvoice->getId())
            ->andWhere('date_diff(si.date, :thisDate) = 0')
            ->setParameter('thisDate', $date->format('Y-m-d'));
        return $qb->getQuery()->getOneOrNullResult();
    }

    /** @return SupplierTransaction[] */
    public function findOverdueInvoices(DateTime $asOf = null)
    {
        if (! $asOf ) $asOf = new DateTime();

        $qb = $this->createQueryBuilder('st');
        $qb->where('st.systemType = :invoiceType')
            ->setParameter('invoiceType', SystemType::PURCHASE_INVOICE)
            ->andWhere('st.settled = 0')
            ->andWhere('st.dueDate < :asOf')
            ->setParameter('asOf', $asOf->format('Y-m-d'));
        return $qb->getQuery()->getResult();
    }

    /**
     * @return SupplierTransaction[] Invoices that the user might want to
     * review for holds.
     */
    public function findInvoicesForHolds(DateTime $start, DateTime $end)
    {
        $qb = $this->createQueryBuilder('invoice');
        $qb->andWhere('invoice.systemType = :invoiceType')
            ->setParameter('invoiceType', SystemType::PURCHASE_INVOICE)
            ->andWhere('(invoice.subtotalAmount + invoice.taxAmount) > invoice.amountAllocated')
            ->andWhere('invoice.date >= :startDate')
            ->setParameter('startDate', $start)
            ->andWhere('invoice.date <= :endDate')
            ->setParameter('endDate', $end)
            ->andWhere('invoice.dueDate is not null')
            ->join('invoice.supplier', 'supplier')
            ->orderBy('supplier.name')
            ->addOrderBy('invoice.dueDate');
        return $qb->getQuery()->getResult();
    }

    /**
     * @return SupplierTransaction[] Invoices that need to be paid as of
     *   $dueDate.
     */
    public function findForPaymentRun(PaymentRun $paymentRun)
    {
        $qb = $this->createQueryBuilder('invoice');
        $qb->andWhere('invoice.systemType = :invoiceType')
            ->setParameter('invoiceType', SystemType::PURCHASE_INVOICE)
            ->andWhere('(invoice.subtotalAmount + invoice.taxAmount) > invoice.amountAllocated')
            ->andWhere('invoice.hold = 0')
            ->andWhere('invoice.dueDate is not null')
            ->andWhere('invoice.dueDate <= :dueDate')
            ->setParameter('dueDate', $paymentRun->dueUntil)
            ->join('invoice.supplier', 'supplier')
            ->andWhere('supplier.name like :matching')
            ->setParameter('matching', "%{$paymentRun->matching}%")
            ->andWhere('supplier.currency = :currency')
            ->setParameter('currency', $paymentRun->currency)
        ;
        return $qb->getQuery()->getResult();
    }
}
