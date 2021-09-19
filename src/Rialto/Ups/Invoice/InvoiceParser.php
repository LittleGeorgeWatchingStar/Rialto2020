<?php


namespace Rialto\Ups\Invoice;


use Rialto\Purchasing\Invoice\SupplierInvoice;

/**
 * For parsing UPS invoices in various formats.
 */
interface InvoiceParser
{
    /**
     * True if this parser can handle the file whose name is given.
     *
     * @param string $filename
     * @return bool
     */
    public function canHandleFile($filename);

    /**
     * @param string $string The invoice data
     * @return SupplierInvoice[]
     */
    public function parseInvoices($string);

    /**
     * Returns all of the invoiced shipments, indexed by invoice no.
     *
     * @param $xmlstring
     * @return InvoiceShipment[][] indexed by invoice no.
     */
    public function parseShipments($string);
}
