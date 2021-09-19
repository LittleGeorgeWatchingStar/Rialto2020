<?php

namespace Rialto\Accounting\Testing;


use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Terms\PaymentTerms;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Legacy\EntityFactory;

class AccountingFactory extends EntityFactory
{
    public function account($id)
    {
        $act = new GLAccount($id);
        return $this->findOrPersist($act, $id);
    }

    public function paymentTerms()
    {
        $terms = new PaymentTerms(PaymentTerms::CC_PREPAID);
        $terms->setName('CC prepaid');
        return $this->findOrPersist($terms, $terms->getId());
    }

    public function currency($code = Currency::USD)
    {
        $currency = new Currency($code, $code);
        return $this->findOrPersist($currency, $currency->getId());
    }

    public function transaction($sysTypeId)
    {
        $sysType = new SystemType($sysTypeId);
        return new Transaction($sysType);
    }

    /**
     * @return BankAccount
     */
    public function bankAccount()
    {
        $account = $this->account(GLAccount::REGULAR_CHECKING_ACCOUNT);
        $bankAccount = BankAccount::fromGLAccount($account);
        return $bankAccount;
    }
}
