<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

/**
 * A strategy for finding the invoice in an email from a supplier.
 *
 * When a supplier sends an invoice email, they might put the invoice
 * document as an attachment, or the might provide a link to the invoice.
 * Implementations of this interface are used to get the invoice document
 * from such an email.
 */
interface AttachmentLocatorStrategy
{
    /**
     * What if someone maliciously emails us a massive file?
     */
    const MAX_ATTACHMENT_SIZE = 100000000; // 100 MB TODO php5.6

    /** @return string */
    public function getLocation();

    /**
     * Finds the invoices in the email message, saves them to the hard disk,
     * and attaches them to the email.
     */
    public function loadAttachments(SupplierEmail $email);
}
