<?php


namespace Rialto\Accounting;


interface InvoiceItem
{
    /**
     * The ID of this invoice item on the original storefront.
     * @return string
     */
    public function getSourceId();

    /**
     * @return int|float
     */
    public function getQtyInvoiced();
}
