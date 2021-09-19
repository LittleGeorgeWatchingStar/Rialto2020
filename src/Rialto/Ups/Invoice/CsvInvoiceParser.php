<?php

namespace Rialto\Ups\Invoice;

use DateTime;
use Gumstix\Filetype\CsvFile;
use Gumstix\Filetype\CsvFileWithHeadings;
use Gumstix\GeographyBundle\Model\BasicAddress;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Supplier\Supplier;

/**
 * Parses UPS CSV invoices.
 */
class CsvInvoiceParser extends InvoiceParserAbstract
{
    /**
     * Invoices indexed by supplier invoice number (aka supplier reference)
     * @var SupplierInvoice[]
     */
    private $invoices;

    /**
     * True if this parser can handle the file whose name is given.
     *
     * @param string $filename
     * @return bool
     */
    public function canHandleFile($filename)
    {
        return is_substring('.csv', $filename);
    }

    public function parseInvoices($string)
    {
        $rows = $this->parseString($string);
        $this->invoices = [];
        foreach ($rows as $row) {
            $this->findOrCreate($row);
        }
        return $this->invoices;
    }

    private function parseString($data)
    {
        $headings = $this->loadHeadings();
        $csv = new CsvFile();
        $csv->parseString($data);
        $rows = [];
        foreach ($csv as $row) {
            // Use the headings as array keys and the row as values.
            $usedHeadings = array_slice($headings, 0, count($row));
            $rows[] = array_combine($usedHeadings, $row);
        }
        return $rows;
    }

    private function loadHeadings()
    {
        $csv = new CsvFileWithHeadings();
        $csv->parseFile(__DIR__ . '/csv_invoice_headers.csv');
        return array_map('strtolower', $csv->getHeadings());
    }

    private function findOrCreate(array $row)
    {
        $invoiceNo = $this->normalize($row["invoice number"]);
        $accountNo = $this->normalize($row["account number"]);
        if (isset($this->invoices[$invoiceNo])) {
            return;
        }
        $supplier = $this->findSupplier($accountNo, $invoiceNo);
        $invoice = $this->findExisting($supplier, $invoiceNo);
        $invoice = $invoice ?: $this->create($supplier, $invoiceNo, $row);
        $this->invoices[$invoiceNo] = $invoice;
    }

    private function create(Supplier $supplier, $invoiceNo, array $row)
    {
        $invDate = $this->parseDate($row['invoice date']);
        $dueDate = $this->parseDate($row['invoice due date']);
        $amount = (float) $row['invoice amount'];
        return $this->createInvoice($supplier, $invoiceNo, $amount, $invDate, $dueDate);
    }

    private function parseDate($dateString)
    {
        return new DateTime(trim($dateString));
    }

    /**
     * Returns all of the invoiced shipments, indexed by invoice no.
     *
     * @param $string
     * @return InvoiceShipment[][] indexed by invoice no.
     */
    public function parseShipments($string)
    {
        $rows = $this->parseString($string);
        /** @var $shipments InvoiceShipment[][] */
        $shipments = [];
        foreach ($rows as $row) {
            $invoiceNo = $this->normalize($row['invoice number']);
            $trackingNo = $this->normalize($row['tracking number']);
            if (!isset($shipments[$invoiceNo][$trackingNo])) {
                $shipment = new InvoiceShipment();
                $shipment->trackingNumber = $trackingNo;
                $shipment->fromAddressee = $row['sender company name'];
                $shipment->fromAttention = $row['sender name'];
                $shipment->fromAddress = $this->parseAddress($row, 'sender');
                $shipment->toAddressee = $row['receiver company name'];
                $shipment->toAttention = $row['receiver name'];
                $shipment->toAddress = $this->parseAddress($row, 'receiver');
                $shipments[$invoiceNo][$trackingNo] = $shipment;
            }
            $shipments[$invoiceNo][$trackingNo]->addDescription($row['charge description']);
        }
        return $shipments;
    }

    private function parseAddress(array $row, $direction)
    {
        if (!trim($row["$direction address line 1"])) {
            return null;
        }
        $address = new BasicAddress();
        $address->setStreet1(trim($row["$direction address line 1"]));
        $address->setStreet2(trim($row["$direction address line 2"]));
        $address->setCity(trim($row["$direction city"]));
        $address->setStateCode(trim($row["$direction state"]));
        $address->setPostalCode(trim($row["$direction postal"]));
        $address->setCountry(trim($row["$direction country"]));
        return $address;
    }
}
