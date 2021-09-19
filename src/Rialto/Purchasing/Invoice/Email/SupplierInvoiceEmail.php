<?php


namespace Rialto\Purchasing\Invoice\Email;


use Rialto\Email\Email;
use Rialto\Email\Mailable\Mailable;

/**
 * An email sent to a supplier or the destination facility regarding an invoice
 * of a purchase order.
 */
final class SupplierInvoiceEmail extends Email
{
    public function __construct(Mailable $sender)
    {
        $this->setFrom($sender);
    }
}
