<?php

namespace Rialto\Accounting\Debtor\Credit;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Creates a credit note for a customer.
 */
class CreditNote extends CustomerCredit
{
    /**
     * @var GLAccount
     * @Assert\NotNull(message="Please choose an account.")
     */
    private $toAccount;

    protected function getMemoForBranch(CustomerBranch $branch)
    {
        return 'Credit note for '. $branch->getBranchName();
    }

    protected function getMemoForOrder(SalesOrder $salesOrder)
    {
        return 'Credit note for sales order '. $salesOrder->getOrderNumber();
    }

    public function setToAccount(GLAccount $toAccount)
    {
        $this->toAccount = $toAccount;
    }

    public function getToAccount()
    {
        return $this->toAccount;
    }

    public function createAdditionalTransactions(Transaction $trans, DbManager $dbm)
    {
        /* Nothing to do. */
    }

    public function getSystemType(DbManager $dbm)
    {
        return SystemType::fetchCreditNote($dbm);
    }
}
