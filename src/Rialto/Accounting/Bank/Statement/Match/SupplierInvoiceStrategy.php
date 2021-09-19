<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Supplier\SupplierPayment;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\IllegalStateException;

/**
 * Implementations of this strategy will create or update supplier
 * invoices to match the bank statement.
 */
abstract class SupplierInvoiceStrategy
extends BankTransactionStrategy
{
    /** @var SupplierTransaction[] */
    protected $invoices;

    /** @var SupplierTransaction[] */
    protected $payments = [];

    protected function initializeCollections()
    {
        parent::initializeCollections();
        $this->invoices = new ArrayCollection();
    }

    protected function getSupplier()
    {
        $supplier = $this->pattern->getSupplier();
        if ( $supplier ) return $supplier;

        throw new IllegalStateException('supplier pattern %s has no supplier',
            $this->pattern->getId()
        );
    }

    public function getSupplierName()
    {
        return $this->getSupplier()->getName();
    }

    protected function recordSupplierPayments()
    {
        foreach ( $this->bankStatements as $statement ) {
            $this->recordSupplierPayment($statement);
        }
    }

    private function recordSupplierPayment(BankStatement $statement)
    {
        if (! $this->company ) {
            throw new IllegalStateException('No company has been set');
        }

        $supplier = $this->getSupplier();
        $amount = -$statement->getAmountOutstanding();
        $date = $statement->getDate();
        $bankAccount = $this->bankAccountRepository->getDefaultChecking();

        $suppPaymentSvc = new SupplierPayment($this->company, $supplier);
        $suppPaymentSvc->setDate($date);
        $suppPaymentSvc->setAccount($bankAccount);
        $suppPaymentSvc->setPaymentType(BankTransaction::TYPE_DIRECT);
        $suppPaymentSvc->setPaymentAmount($amount);
        $suppPaymentSvc->setMemo($statement->getDescription());

        $this->payments[] = $suppPaymentSvc->createPayment($this->dbm);
        $this->bankTransactions[] = $suppPaymentSvc->getBankTransaction();
    }

    protected function linkSupplierTransactions()
    {
        foreach ( $this->invoices as $invoice ) {
            $this->linkSupplierTransaction($invoice);
        }
    }

    private function linkSupplierTransaction(SupplierTransaction $invoice)
    {
        foreach ( $this->payments as $payment ) {
            if ( $invoice->getAmountUnallocated() == 0 ) return;
            if ( $payment->getAmountUnallocated() == 0 ) continue;

            $invoice->allocateFrom($payment);
        }
    }
}
