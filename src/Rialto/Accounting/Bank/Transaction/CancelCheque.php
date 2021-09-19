<?php

namespace Rialto\Accounting\Bank\Transaction;

use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Supplier\SupplierTransactionRepository;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;

class CancelCheque
{
    private $dbm;
    private $company;

    public function __construct(DbManager $dbm, Company $company)
    {
        $this->dbm = $dbm;
        $this->company = $company;
    }

    public function cancel(BankTransaction $cheque)
    {
        $error = $this->validateCheque($cheque);
        if ($error) {
            throw new \InvalidArgumentException($error);
        }

        $payment = $this->getSupplierPayment($cheque);
        $this->reverseLedgerEntries($cheque, $payment);

        $cheque->cancel();
        $this->dbm->flush();

        $payment->cancelPayment();
        $this->dbm->flush();
    }

    public function validateCheque(BankTransaction $bankTrans)
    {
        if (! $bankTrans->isCheque()) {
            return sprintf("Bank transaction %s is not a cheque.",
                $bankTrans->getId()
            );
        }
        if (! $bankTrans->isOutstanding()) {
            return sprintf("Bank transaction %s is not outstanding.",
                $bankTrans->getId()
            );
        }
        return null;
    }

    /** @return SupplierTransaction */
    private function getSupplierPayment(BankTransaction $cheque)
    {
        /** @var $repo SupplierTransactionRepository */
        $repo = $this->dbm->getRepository(SupplierTransaction::class);
        $payment = $repo->findSupplierPaymentByCheque($cheque);
        if ($payment) {
            return $payment;
        }

        throw new \UnexpectedValueException(sprintf(
            "Unable to find payment for bank transaction %s",
            $cheque->getId()
        ));
    }

    private function reverseLedgerEntries(
        BankTransaction $cheque,
        SupplierTransaction $payment)
    {
        $glTrans = Transaction::fromEvent($payment);
        $fromAccount = $this->company->getCreditorsAccount();
        $toAccount = $cheque->getBankAccount();
        /* Remember: cheque and payments have a negative amount. */
        $amount = -$cheque->getAmount();
        $memo = "Cancel cheque #" . $cheque->getChequeNumber();
        $glTrans->addEntry($fromAccount, -$amount, $memo);
        $glTrans->addEntry($toAccount->getGLAccount(), $amount, $memo);
        $this->dbm->persist($glTrans);
    }
}
