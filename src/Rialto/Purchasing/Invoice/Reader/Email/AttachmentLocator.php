<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

/**
 * Extracts the invoice attachments from a supplier email.
 */
class AttachmentLocator implements AttachmentLocatorInterface
{
    /** @var AttachmentLocatorStrategy[] */
    private $strategies = [];

    public function registerStrategy(AttachmentLocatorStrategy $strategy)
    {
        $this->strategies[ $strategy->getLocation() ] = $strategy;
    }

    public function loadAttachments(SupplierEmail $email)
    {
        $pattern = $email->getPattern();
        assertion($pattern !== null);
        $strategy = $this->getInvoiceLocationStrategy($pattern->getLocation());
        $strategy->loadAttachments($email);
    }

    /** @return AttachmentLocatorStrategy */
    private function getInvoiceLocationStrategy($location)
    {
        if ( isset($this->strategies[$location]) ) {
            return $this->strategies[$location];
        }

        throw new \UnexpectedValueException("No strategy registered for location \"$location\"");
    }

}
