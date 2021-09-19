<?php

namespace Rialto\Accounting\PaymentTransaction;

use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Ledger\Entry\GLEntry;


/**
 * Shared interface for DebtorTransaction and SupplierTransaction.
 */
interface PaymentTransaction extends AccountingEvent
{
    /** @return int */
    public function getId();

    public function isCredit(): bool;

    public function isInvoice(): bool;

    /**
     * @return PaymentAllocation[]
     */
    public function getAllocations(): array;

    public function addAllocation(PaymentAllocation $alloc);

    public function removeAllocation(PaymentAllocation $alloc);

    /**
     * Update the amount allocated and the settled flag to match the current
     * allocations.
     */
    public function updateAmountAllocated();

    public function isSettled(): bool;

    /**
     * @return GLEntry[]
     */
    public function getGLEntries(): array;

    /**
     * @return BankTransaction[]
     */
    public function getBankTransactions(): array;

    public function getTotalAmount(): float;

    public function getSubtotalAmount(): float;

    public function getTaxAmount(): float;

    public function getAmountAllocated(): float;

    public function getAmountUnallocated(): float;

    public function isFullyAllocated(): bool;

    public function getLabel(): string;

    public function getSummary(): string;
}
