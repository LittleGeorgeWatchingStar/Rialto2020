<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

/**
 * Extracts the invoice attachments from a supplier email.
 */
interface AttachmentLocatorInterface
{
    public function loadAttachments(SupplierEmail $email);
}
