<?php

namespace Rialto\Sales\GLPosting;

use Rialto\Accounting\Ledger\Account\GLAccount;

/**
 * This subclass of GLPosting determines the stock accounts to use
 * when doing sales order accounting.
 */
class CogsGLPosting extends GLPosting
{
    /**
     * The account to use for Cost of Goods Sold (COGS) when
     * invoicing a sales order.
     *
     * @var GLAccount
     */
    private $account;

    /** @return GLAccount */
    public function getAccount()
    {
        return $this->account;
    }
}
