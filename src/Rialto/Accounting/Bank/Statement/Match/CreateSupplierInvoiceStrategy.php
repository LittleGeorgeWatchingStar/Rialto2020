<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\IllegalStateException;

/**
 * This strategy creates a new supplier invoice and bank transaction
 * to match the bank statement.
 */
class CreateSupplierInvoiceStrategy
extends SupplierInvoiceStrategy
{
    const INVOICES = 'invoices';
    const PAYMENTS = 'payments';

    private $createInvoices = null;

    public function hasMatchingRecords(): bool
    {
        /* This strategy does not need to find matching records to be
         * applicable -- it will create the records it needs. */
        return true;
    }

    public function getCreateInvoices()
    {
        return $this->createInvoices;
    }

    public function setCreateInvoices($createInvoice)
    {
        $this->createInvoices = $createInvoice;
    }

    public function save()
    {
        if ( $this->createInvoices ) {
            if ( $this->createInvoices == self::INVOICES ) {
                $this->recordSupplierInvoices();
            }
            $this->recordSupplierPayments();
            $this->linkSupplierTransactions();
        }
        $this->linkBankTransactions();
    }

    private function recordSupplierInvoices()
    {
        foreach ( $this->bankStatements as $statement ) {
            $this->recordSupplierInvoice($statement);
        }
    }

    private function recordSupplierInvoice(BankStatement $statement)
    {
        if (! $this->company ) {
            throw new IllegalStateException('No company has been set');
        }

        $supplier = $this->getSupplier();
        $expenseAccount = $this->pattern->getAdjustmentAccount();
        $date = $statement->getDate();
        $amount = abs($statement->getAmountOutstanding());

        $sysType = SystemType::fetchPurchaseInvoice($this->dbm);

        $glTrans = new Transaction($sysType);
        $glTrans->setDate($date);

        $suppTrans = new SupplierTransaction($glTrans, $supplier);
        $suppTrans->setSubtotalAmount($amount);
        $suppTrans->setMemo($statement->getDescription());
        $suppTrans->setReference($supplier->getId());
        $this->dbm->persist($suppTrans);

        $total = $suppTrans->getTotalAmount();
        $glMemo = sprintf('%s - %s', $supplier->getId(), $suppTrans->getMemo());
        $glTrans->addEntry($expenseAccount, $total, $glMemo);
        $creditorAccount = $this->company->getCreditorsAccount();
        $glTrans->addEntry($creditorAccount, -$total, $glMemo);
        $this->dbm->persist($glTrans);

        $this->invoices[] = $suppTrans;
    }

}
