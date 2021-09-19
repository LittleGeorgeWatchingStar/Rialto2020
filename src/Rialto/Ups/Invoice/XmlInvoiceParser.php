<?php

namespace Rialto\Ups\Invoice;

use DateTime;
use Gumstix\GeographyBundle\Model\BasicAddress;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Supplier\Supplier;
use SimpleXMLElement;

/**
 * Parses UPS XML invoices.
 */
class XmlInvoiceParser extends InvoiceParserAbstract
{
    /**
     * True if this parser can handle the file whose name is given.
     *
     * @param string $filename
     * @return bool
     */
    public function canHandleFile($filename)
    {
        return is_substring('.xml', $filename);
    }

    /**
     * @param string $xmlstring The contents of an XML invoice file
     * @return SupplierInvoice[] The invoices contained within the file.
     */
    public function parseInvoices($xmlstring)
    {
        $xml = $this->createDom($xmlstring);
        $invoices = [];
        foreach ($xml->xpath('//d:Invoice') as $inv) {
            $invoice = $this->findOrCreate($inv);
            $invoices[$invoice->getSupplierReference()] = $invoice;
        }
        return $invoices;
    }

    /** @return SimpleXMLElement */
    private function createDom($xmlstring)
    {
        $xml = new SimpleXMLElement($xmlstring);
        foreach ($xml->getNamespaces() as $prefix => $uri) {
            $prefix = $prefix ?: 'd';
            $xml->registerXPathNamespace($prefix, $uri);
        }
        return $xml;
    }

    private function findOrCreate(SimpleXMLElement $inv)
    {
        $invoiceNo = $this->normalize($inv->InvoiceNumber);
        $accountNo = $this->normalize($inv->Account->AccountNumber);
        $supplier = $this->findSupplier($accountNo, $invoiceNo);
        $invoice = $this->findExisting($supplier, $invoiceNo);
        return $invoice ?: $this->create($supplier, $invoiceNo, $inv);
    }

    private function create(Supplier $supplier, $invoiceNo, SimpleXMLElement $inv)
    {
        $invDate = new DateTime(trim($inv->InvoiceDateCCYYMMDD));
        $dueDate = new DateTime(trim($inv->InvoiceDueDateCCYYMMDD));
        $amount = (float) $inv->InvoiceAmount;
        return $this->createInvoice($supplier, $invoiceNo, $amount, $invDate, $dueDate);
    }

    /**
     * Returns all of the invoiced shipments, indexed by invoice no.
     *
     * @param $xmlstring
     * @return InvoiceShipment[] indexed by invoice no.
     */
    public function parseShipments($xmlstring)
    {
        $xml = $this->createDom($xmlstring);
        $shipments = [];
        foreach ($xml->xpath('//d:Invoice') as $inv) {
            $invoiceNo = $this->normalize($inv->InvoiceNumber);
            $shipments[$invoiceNo] = $this->createShipments($inv);
        }
        return $shipments;
    }

    /** @return InvoiceShipment[] */
    private function createShipments(SimpleXMLElement $inv)
    {
        $shipments = [];
        foreach ($inv->TransactionDetails as $trans) {
            foreach ($trans->Shipment as $sh) {
                $shipment = new InvoiceShipment();
                $shipment->trackingNumber = trim($sh->LeadShipmentNumber);

                $from = $sh->AddressDetails->SenderAddress;
                if ($from) {
                    $shipment->fromAddressee = isset($from->Addressee) ? trim($from->Addressee->Name) : null;
                    $shipment->fromAttention = isset($from->Attention) ? trim($from->Attention->Name) : null;
                    $shipment->fromAddress = $this->parseAddress($from->Address);
                }

                $to = $sh->AddressDetails->ReceiverAddress;
                if ($to) {
                    $shipment->toAddressee = isset($to->Addressee) ? trim($to->Addressee->Name) : null;
                    $shipment->toAttention = isset($to->Attention) ? trim($to->Attention->Name) : null;
                    $shipment->toAddress = $this->parseAddress($to->Address);
                }

                $shipments[] = $shipment;
            }
        }
        return $shipments;
    }

    private function parseAddress(SimpleXMLElement $addr = null)
    {
        if (null === $addr) {
            return null;
        }
        $address = new BasicAddress();
        $address->setStreet1(trim($addr->StreetAddress));
        $address->setStreet2(trim($addr->AddressText2));
        $address->setCity(trim($addr->CityName));
        $address->setStateCode(trim($addr->StateCode));
        $address->setPostalCode(trim($addr->PostalCode));
        $address->setCountry(trim($addr->CountryCode));
        return $address;
    }
}
