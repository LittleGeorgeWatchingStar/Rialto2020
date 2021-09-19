<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use Rialto\Purchasing\Invoice\Parser\SupplierInvoiceParser;
use UnexpectedValueException;

/**
 * Responsible for locating attachments in supplier invoice emails, parsing
 * them and making them accessible so that they can be imported.
 */
class AttachmentParser
{
    /** @var AttachmentLocatorInterface */
    private $locator;

    /** @var AttachmentConverter */
    private $converter;

    /** @var SupplierInvoiceParser */
    private $parser;

    public function __construct(
        AttachmentLocatorInterface $locator,
        AttachmentConverter $converter,
        SupplierInvoiceParser $parser)
    {
        $this->locator = $locator;
        $this->converter = $converter;
        $this->parser = $parser;
    }

    /**
     * Parses the attachments of $email in search of invoices and
     * attaches any invoices found directly to $email.
     *
     * Any invoices found will be accessible via $email->getInvoices().
     */
    public function findInvoices(SupplierEmail $email)
    {
        $this->locator->loadAttachments($email);
        $this->converter->convertAttachments($email);
        $this->parseAttachments($email);
    }

    private function parseAttachments(SupplierEmail $email)
    {
        $pattern = $email->getPattern();

        foreach ( $email->getAttachments() as $attachment ) {
            if (! $attachment->getData() ) {
                continue;
            }
            try {
                $invoices = $this->parser->parse($pattern, $attachment->getData());
                foreach ( $invoices as $invoice ) {
                    $invoice->setFilename($attachment->getFilename());
                    $attachment->addInvoice($invoice);
                }
                foreach ( $this->parser->getErrors() as $error ) {
                    $attachment->addError($error);
                }
            }
            catch ( UnexpectedValueException $ex ) {
                $attachment->addError($ex);
            }
        }
    }
}
