<?php

namespace Rialto\Sales;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Filing\DocumentGenerator;
use Rialto\Sales\Invoice\SalesInvoice;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Sales\Returns\SalesReturn;
use Rialto\Sales\Returns\SalesReturnRepository;
use Rialto\Sales\Shipping\ShippableOrderItem;
use Rialto\Shipping\Export\Document\ElectronicExportInformation;

class SalesPdfGenerator
{
    /** @var DocumentGenerator */
    private $docGenerator;

    /** @var PdfGenerator */
    private $pdfGenerator;

    /** @var DbManager */
    private $dbm;

    public function __construct(
        DocumentGenerator $docGenerator,
        PdfGenerator $pdfGenerator,
        DbManager $dbm)
    {
        $this->docGenerator = $docGenerator;
        $this->pdfGenerator = $pdfGenerator;
        $this->dbm = $dbm;
    }

    /**
     * @return string PDF data
     */
    public function generateDebtorTransactionPdf(DebtorTransaction $debtorTrans)
    {
        return $this->pdfGenerator->render(
            'accounting/debtor/transaction/pdf.tex.twig', [
            'debtorTrans' => $debtorTrans,
        ]);
    }

    /**
     * @param SalesInvoice $invoice
     * @return string PDF data
     */
    public function generateCommercialInvoicePdf(SalesInvoice $invoice)
    {
        return $this->pdfGenerator->render(
            'ups/invoice/CommercialInvoice.pdf.twig', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * @return string PDF data
     */
    public function generateSedFormPdf(ElectronicExportInformation $eei)
    {
        $address = $eei->getDeliveryAddress();
        $header = [
            'CustomerName' => $eei->getDeliveryName(),
            'CustomerAddress' => $this->getStreetAddress($address),
            'CityStateZip' => $this->getCityStateZip($address),
            'Date' => date('m/d/Y'),
            'UPS_Number' => $eei->getTrackingNumber(),
            'ExportLicense' => '', // TODO: what should go here?
            'Country' => $address->getCountryCode(),
            'EccnCode' => $eei->getEccnCode(),
            'BondCode' => $eei->getBondCode(),
        ];

        $lineItems = [];
        foreach ($eei->getEeiLineItems() as $eeiItem) {
            $lineItems[] = $this->getItemData($eeiItem);
        }

        return $this->docGenerator->generate('sed.png', $header, $lineItems);
    }

    private function getStreetAddress(PostalAddress $address)
    {
        $parts = [
            $address->getStreet1(),
            $address->getStreet2(),
            $address->getMailStop(),
        ];
        return join(', ', array_filter($parts));
    }

    private function getCityStateZip(PostalAddress $address)
    {
        $parts = [
            $address->getCity(),
            $address->getStateCode(),
            $address->getPostalCode(),
        ];
        return join(', ', array_filter($parts));
    }

    private function getItemData(ShippableOrderItem $sedItem)
    {
        return [
            'StockID' => $sedItem->getSku(),
            'Description' => $sedItem->getDescription(),
            'Quantity' => $sedItem->getQtyToShip(),
            'Value' => $sedItem->getExtendedPrice(),
            'Weight' => $sedItem->getTotalWeight(),
            'Harmonization' => $sedItem->getHarmonizationCode(),
        ];
    }

    public function generatePdf(SalesOrder $order, $pdfType)
    {
        $salesReturn = null;
        if ($order->isReplacement()) {
            /** @var $repository SalesReturnRepository */
            $repository = $this->dbm->getRepository(SalesReturn::class);
            $salesReturn = $repository->findOneByReplacementOrder($order);
        }

        return $this->pdfGenerator->render('sales/order/pdf.tex.twig', [
            'order' => $order,
            'pdfType' => $pdfType,
            'salesStage' => $this->getSalesStage($order, $pdfType),
            'salesReturn' => $salesReturn,
        ]);
    }

    private function getSalesStage(SalesOrder $order, $pdfType)
    {
        switch (trim($pdfType)) {
            case SalesOrder::QUOTATION:
                return 'quotation';
            case '':
                return $order->getSalesStageLabel();
            default:
                return 'order';
        }
    }

    public function generatePackingSlip(SalesOrderInterface $order)
    {
        return $this->pdfGenerator->render('sales/order/packing-slip.tex.twig', [
            'order' => $order,
        ]);
    }

}
