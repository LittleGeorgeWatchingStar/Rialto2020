<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;


use Rialto\Purchasing\Invoice\SupplierInvoice;

class FakeAttachmentParser extends AttachmentParser
{
    /**
     * @var SupplierInvoice[]
     */
    private $invoices = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
    }

    public function addInvoice(SupplierInvoice $invoice)
    {
        $this->invoices[] = $invoice;
    }

    public function findInvoices(SupplierEmail $email)
    {
        $locator = new FakeAttachmentLocator();
        $locator->loadAttachments($email);
        foreach ($email->getAttachments() as $attachment) {
            foreach ($this->invoices as $invoice) {
                $attachment->addInvoice($invoice);
            }
        }
    }
}
