<?php

namespace Rialto\Ups\Invoice;


use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;

class InvoiceParseException extends \UnexpectedValueException
{
    public function __construct($accountNo, UnexpectedResultException $previous)
    {
        $message = $previous instanceof NoResultException
            ? "No supplier was found with account number '$accountNo'"
            : "Multiple suppliers were found with account number '$accountNo'";
        parent::__construct($message, null, $previous);
    }
}
