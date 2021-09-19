<?php

namespace Rialto\Sales\GLPosting;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This subclass of GLPosting determines the sales and discount accounts
 * to use when doing sales order accounting.
 *
 * @UniqueEntity(fields={"salesArea", "salesType", "stockCategory"},
 *   ignoreNull=false,
 *   message="There is already a record for that area, type, and category.")
 */
class SalesGLPosting extends GLPosting
{
    /**
     * The account to use for sales amounts when doing sales order
     * accounting.
     *
     * @var GLAccount
     * @Assert\NotNull
     */
    private $salesAccount;

    /**
     * The account to use for discount amounts when doing sales order
     * accounting.
     *
     * @var GLAccount
     * @Assert\NotNull
     */
    private $discountAccount;


    /** @return GLAccount */
    public function getSalesAccount()
    {
        return $this->salesAccount;
    }

    public function setSalesAccount(GLAccount $salesAccount)
    {
        $this->salesAccount = $salesAccount;
    }

    /** @return GLAccount */
    public function getDiscountAccount()
    {
        return $this->discountAccount;
    }

    public function setDiscountAccount(GLAccount $discountAccount)
    {
        $this->discountAccount = $discountAccount;
    }
}
