<?php

namespace Rialto\Purchasing\Invoice;

use Exception;
use SplFileInfo;

/**
 *
 */
class SupplierEmailAttachment
{
    /** @var int */
    private $partNo;

    /**
     * The original attachment file.
     *
     * @var SplFileInfo
     */
    private $original;

    /**
     * The parsed CSV file.
     * @var SplFileInfo
     */
    private $csv = null;

    /**
     * The final parseable data.
     * @var string[][]
     */
    private $data = null;

    /** @var SupplierInvoice[] */
    private $invoices = [];

    /** @var Exception[] */
    private $errors = [];

    public function __construct($partNo, SplFileInfo $original)
    {
        $this->partNo = $partNo;
        $this->original = $original;
    }

    public function getPartNo()
    {
        return $this->partNo;
    }

    /** @return SplFileInfo */
    public function getOriginal()
    {
        return $this->original;
    }

    public function getFilename(): string
    {
        return $this->original->getBasename();
    }

    public function hasCsv()
    {
        return null !== $this->csv;
    }

    public function getCsv()
    {
        return $this->csv;
    }

    public function setCsv(\SplFileInfo $csv)
    {
        $this->csv = $csv;
    }

    /**
     * @return string[][] The final parseable data.
     */
    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    /** @return SupplierInvoice[] */
    public function getInvoices()
    {
        return $this->invoices;
    }

    public function addInvoice(SupplierInvoice $invoice)
    {
        $key = $invoice->getIndexKey();
        $this->invoices[$key] = $invoice;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addError(Exception $error)
    {
        $this->errors[] = $error;
    }

}
