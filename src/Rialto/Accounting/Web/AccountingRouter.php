<?php

namespace Rialto\Accounting\Web;

use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Transaction\Transaction;
use Symfony\Component\Routing\RouterInterface;

/**
 * Generates URLs for commonly-used accounting pages.
 */
class AccountingRouter
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function debtorTransView(DebtorTransaction $trans)
    {
        return $this->router->generate('debtor_transaction_view', [
            'trans' => $trans->getId(),
        ]);
    }

    public function supplierTransView(SupplierTransaction $trans)
    {
        return $this->router->generate('supplier_transaction_view', [
            'trans' => $trans->getId(),
        ]);
    }

    public function bankStatementView(BankStatement $statement)
    {
        return $this->router->generate('bank_statement_view', [
            'id' => $statement->getId(),
        ]);
    }

    public function bankTransactionView(BankTransaction $trans)
    {
        return $this->router->generate('bank_transaction_view', [
            'id' => $trans->getId(),
        ]);
    }

    public function transactionView(Transaction $trans)
    {
        return $this->router->generate('transaction_view', [
            'trans' => $trans->getId(),
        ]);
    }
}
