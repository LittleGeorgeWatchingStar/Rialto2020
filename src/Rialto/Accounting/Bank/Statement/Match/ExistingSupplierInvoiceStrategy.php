<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Doctrine\Common\Collections\Collection;
use Rialto\Accounting\Supplier\SupplierInvoiceAdjustment;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Supplier\SupplierTransactionRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Matches a bank statement item against an existing supplier invoice
 * and then creates a bank transaction to match.
 */
class ExistingSupplierInvoiceStrategy
extends SupplierInvoiceStrategy
{
    private $matchingSupp = [];

    public function loadMatchingRecords()
    {
        if (! $this->loadMatchingBankTransactions() ) {
            /** @var SupplierTransactionRepository $repo */
            $repo = $this->dbm->getRepository(SupplierTransaction::class);
            $this->matchingSupp = $repo->findMatchingInvoices($this->pattern, $this);
        }
    }

    public function hasMatchingRecords(): bool
    {
        return parent::hasMatchingRecords() ||
            ( count($this->matchingSupp) > 0 );
    }

    public function getMatchingSupplierInvoices()
    {
        return $this->matchingSupp;
    }

    public function getAcceptedSupplierInvoices()
    {
        return $this->invoices;
    }

    public function setAcceptedSupplierInvoices(Collection $invoices)
    {
        $this->invoices = $invoices;
    }

    public function canUpdateInvoice(SupplierTransaction $invoice)
    {
        return $this->pattern->matchesUpdatePattern($invoice->getReference());
    }

    public function save()
    {
        if ( count($this->invoices) > 0 ) {
            $this->adjustInvoiceAmountsIfNeeded();
            $this->recordSupplierPayments();
            $this->linkSupplierTransactions();
        }
        $this->linkBankTransactions();
    }

    private function adjustInvoiceAmountsIfNeeded()
    {
        foreach ( $this->invoices as $suppInvoice ) {
            $this->adjustInvoiceAmount($suppInvoice);
        }
    }

    private function adjustInvoiceAmount(SupplierTransaction $suppInvoice)
    {
        if (! $this->canUpdateInvoice($suppInvoice) ) return;

        /* Remember: the bank statement amount is negative (because it is
         * a payment to the supplier), while the supplier invoice amount is
         * positive. */
        $total = -$this->getTotalOutstanding();
        $diff = $total - $this->getSupplierInvoiceTotal();
        if ( $this->round($diff) == 0 ) return;

        $adjustment = new SupplierInvoiceAdjustment($this->dbm, $this->company, $suppInvoice);
        $adjustment->setMemo('Adjustment to match bank statement');
        $adjustment->setSubtotalAdjustment($diff);
        $adjustment->setExpenseAccount( $this->pattern->getAdjustmentAccount() );
        $adjustment->save();
    }

    private function getSupplierInvoiceTotal()
    {
        $total = 0;
        foreach ( $this->invoices as $invoice ) {
            $total += $invoice->getAmountUnallocated();
        }
        return $total;
    }

    /**
     * @Assert\Callback
     */
    public function validateAdjustmentAmount(ExecutionContextInterface $context)
    {
        $outstanding = -$this->getTotalOutstanding();
        $adjustmentAmount = $outstanding - $this->getSupplierInvoiceTotal();
        foreach ( $this->invoices as $invoice ) {
            if (! $this->canUpdateInvoice($invoice) ) continue;

            if ( $invoice->getSubtotalAmount() + $adjustmentAmount < 0 ) {
                $context->addViolation('The amounts for statement _id do not match', [
                    '_id' => $this->getStatement()->getId()
                ]);
            }
        }
    }
}
