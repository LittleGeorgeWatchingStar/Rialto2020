<?php

namespace Rialto\Accounting\Debtor\Credit;

use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\DbManager;
use Rialto\Email\Mailable\Mailable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Creates the accounting records needed to record the receipt of a
 * payment from a customer.
 */
abstract class CustomerReceipt
extends CustomerCredit
implements Mailable
{
    public function getSystemType(DbManager $dbm)
    {
        return SystemType::fetchReceipt($dbm);
    }

    /**
     * A description of the type of receipt this is (eg, cheque, wire transfer).
     *
     * @return string
     */
    public abstract function getDescription();

    /**
     * @Assert\NotBlank(message="Selected order or branch has no email address.")
     * @Assert\Email(message="Selected order or branch has an invalid email address.")
     */
    public function getEmail()
    {
        return $this->salesOrder ? $this->salesOrder->getEmail() :
            $this->customer->getEmail();
    }

    public function getName()
    {
        return $this->salesOrder ? $this->salesOrder->getContactName() :
            $this->customer->getName();
    }

}
