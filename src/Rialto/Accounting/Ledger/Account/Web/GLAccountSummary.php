<?php

namespace Rialto\Accounting\Ledger\Account\Web;


use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Web\Serializer\ListableFacade;

class GLAccountSummary
{
    use ListableFacade;

    /** @var GLAccount */
    private $account;

    public function __construct(GLAccount $account)
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

    public function getGroupName()
    {
        return $this->account->getGroupName();
    }

    public function getSectionName()
    {
        return $this->account->getSectionName();
    }
}
