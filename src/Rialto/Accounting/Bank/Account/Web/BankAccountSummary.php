<?php

namespace Rialto\Accounting\Bank\Account\Web;


use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Web\Serializer\ListableFacade;

class BankAccountSummary
{
    use ListableFacade;

    /**
     * @var BankAccount
     */
    private $account;

    public function __construct(BankAccount $account)
    {
        $this->account = $account;
    }

    public function getId()
    {
        return $this->account->getId();
    }

    public function getName()
    {
        return $this->account->getName();
    }

    public function getNextChequeNumber()
    {
        return $this->account->getNextChequeNumber();
    }
}
