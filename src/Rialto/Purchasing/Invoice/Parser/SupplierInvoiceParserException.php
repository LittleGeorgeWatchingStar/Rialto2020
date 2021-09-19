<?php

namespace Rialto\Purchasing\Invoice\Parser;

class SupplierInvoiceParserException extends \UnexpectedValueException
{
    /**
     * Factory method.
     */
    public static function fromPrevious(\Exception $prev)
    {
        return new self($prev->getMessage(), $prev->getCode(), $prev);
    }
}
