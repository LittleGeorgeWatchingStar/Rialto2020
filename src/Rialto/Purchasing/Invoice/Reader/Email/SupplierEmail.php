<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use JMS\Serializer\Annotation as Serialize;
use Rialto\Purchasing\Invoice\SupplierEmailAttachment;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Rialto\Purchasing\Supplier\Supplier;
use Zend\Mail\Storage\Part\PartInterface as MessageInterface;

/**
 * Represents an email message that we received from a supplier, typically
 * to send us an invoice.
 *
 * @see SupplierInvoice
 * @Serialize\ExclusionPolicy("all")
 */
class SupplierEmail
{
    private $messageNo;

    /** @var MessageInterface */
    private $message;

    /** @var SupplierInvoicePattern */
    private $pattern;

    /** @var Supplier */
    private $supplier;

    /** @var SupplierEmailAttachment[] */
    private $attachments = [];

    /** @var SupplierInvoice[] */
    private $finishedInvoices = [];

    private static $dateFormats = [
        'j M y H:i:s+',
    ];

    public function __construct($messageNo, MessageInterface $message)
    {
        $this->messageNo = $messageNo;
        $this->message = $message;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setPattern(SupplierInvoicePattern $pattern)
    {
        $this->pattern = $pattern;
        $this->supplier = $pattern->getSupplier();
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @Serialize\VirtualProperty
     */
    public function getSupplierName()
    {
        $supplier = $this->getSupplier();
        return $supplier ? $supplier->getName() : '';
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @Serialize\VirtualProperty
     */
    public function getMessageNo()
    {
        return $this->messageNo;
    }

    /**
     * @Serialize\VirtualProperty
     */
    public function getMessageId(): string
    {
        return $this->message->getHeader('message-id', 'string');
    }

    /**
     * @Serialize\VirtualProperty
     */
    public function getFrom()
    {
        return $this->message->from;
    }

    /**
     * Returns null if the date cannot be parsed.
     * @return \DateTime|null
     * @Serialize\VirtualProperty
     */
    public function getDate()
    {
        $timestamp = strtotime($this->message->date);
        if ( false === $timestamp ) {
            return $this->parseDate();
        }
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        return $date;
    }

    private function parseDate()
    {
        foreach ( self::$dateFormats as $format ) {
            $date = \DateTime::createFromFormat($format, $this->message->date);
            if ( false !== $date ) return $date;
        }
        return null;
    }

    public function getDateString(): string
    {
        return $this->message->date;
    }

    /**
     * @Serialize\VirtualProperty
     */
    public function getSubject()
    {
        return $this->message->subject;
    }

    /**
     * @return boolean
     */
    public function hasReferences()
    {
        return isset($this->message->references);
    }

    public function getReferences()
    {
        return $this->message->references;
    }

    /** @return SupplierEmailAttachment */
    public function addAttachment($partNo, \SplFileInfo $file)
    {
        $attachment = new SupplierEmailAttachment($partNo, $file);
        $this->attachments[$partNo] = $attachment;
        return $attachment;
    }

    /** @return SupplierEmailAttachment[] */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /** @return SupplierInvoice[] */
    public function getInvoices()
    {
        $invoices = [];
        foreach ( $this->attachments as $attachment ) {
            foreach ($attachment->getInvoices() as $inv ) {
                $invoices[$inv->getIndexKey()] = $inv;
            }
        }
        return $invoices;
    }

    public function hasInvoices()
    {
        return count($this->getInvoices()) > 0;
    }

    /** @return SupplierInvoice[] */
    public function getFinishedInvoices()
    {
        return $this->finishedInvoices;
    }

    public function addFinishedInvoice(SupplierInvoice $invoice)
    {
        assertion($invoice->getId());
        $key = $invoice->getIndexKey();
        $this->finishedInvoices[$key] = $invoice;
    }
}
