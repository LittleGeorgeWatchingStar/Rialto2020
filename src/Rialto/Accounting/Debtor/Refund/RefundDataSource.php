<?php

namespace Rialto\Accounting\Debtor\Refund;


use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;

/**
 * Fetches the data needed to create a customer refund.
 *
 * @see CustomerRefund
 */
class RefundDataSource
{
    /** @var DbManager */
    private $dbm;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    public function getSystemType()
    {
        return SystemType::fetchCustomerRefund($this->dbm);
    }

    public function getDebtorAccount()
    {
        $company = Company::findDefault($this->dbm);
        return $company->getDebtorAccount();
    }

    public function getPrepaidAccount()
    {
        return GLAccount::fetchPrepaidRevenue($this->dbm);
    }

    public function persist($object)
    {
        $this->dbm->persist($object);
    }
}
