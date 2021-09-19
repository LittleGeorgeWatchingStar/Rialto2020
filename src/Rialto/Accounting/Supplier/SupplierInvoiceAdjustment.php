<?php

namespace Rialto\Accounting\Supplier;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;

/**
 * Adjusts a supplier invoice after it has already been entered.
 */
class SupplierInvoiceAdjustment
{
    /** @var DbManager */
    private $dbm;

    /** @var SupplierTransaction */
    private $suppTrans;

    /** @var double */
    private $subtotalAdjustment;

    /** @var GLAccount */
    private $accountsPayable;

    /** @var GLAccount */
    private $expenseAccount;

    /** @var string */
    private $memo;

    public function __construct(
        DbManager $dbm,
        Company $company,
        SupplierTransaction $suppTrans)
    {
        $error = $this->validateTransaction($suppTrans);
        if ( $error ) {
            throw new \InvalidArgumentException($error);
        }

        $this->dbm = $dbm;
        $this->accountsPayable = $company->getCreditorsAccount();
        $this->suppTrans = $suppTrans;
        $this->memo = $suppTrans->getMemo() ?: $suppTrans->getReference();
    }

    private function validateTransaction(SupplierTransaction $suppTrans)
    {
        if (! $suppTrans->isInvoice() ) {
            return sprintf('SupplierTransaction %s is not an invoice',
                $suppTrans->getId()
            );
        }
        return null;
    }

    public function getSuppTrans()
    {
        return $this->suppTrans;
    }

    public function getSubtotalAdjustment()
    {
        return $this->subtotalAdjustment;
    }

    public function setSubtotalAdjustment($diff)
    {
        $this->subtotalAdjustment = $diff;
    }

    public function getExpenseAccount()
    {
        return $this->expenseAccount;
    }

    public function setExpenseAccount(GLAccount $account)
    {
        $this->expenseAccount = $account;
    }

    public function getMemo()
    {
        return $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = $memo;
    }

    public function save()
    {
        $error = $this->validateAdjustmentAmount();
        if ( $error ) throw new \InvalidArgumentException($error);
        $newSubtotal = $this->suppTrans->getSubtotalAmount() + $this->subtotalAdjustment;
        $this->suppTrans->setSubtotalAmount($newSubtotal);

        $glTrans = Transaction::fromEvent($this->suppTrans);
        $glTrans->setMemo($this->memo);

        $glTrans->addEntry($this->expenseAccount, $this->subtotalAdjustment, $this->memo);
        $glTrans->addEntry($this->accountsPayable, -$this->subtotalAdjustment, $this->memo);
        $this->dbm->persist($glTrans);
    }

    private function validateAdjustmentAmount()
    {
        $newSubtotal = $this->suppTrans->getSubtotalAmount() + $this->subtotalAdjustment;
        if ( $newSubtotal < 0 ) {
            return sprintf("Adjustment amount %s cannot be greater than subtotal amount %s",
                number_format(abs($this->subtotalAdjustment), 2),
                number_format($this->suppTrans->getSubtotalAmount(), 2)
            );
        }
        return null;
    }
}
