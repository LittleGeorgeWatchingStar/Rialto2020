<?php

namespace Rialto\Sales;


use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Printing\Job\PrintJob;
use Rialto\Printing\Job\PrintQueue;
use Rialto\Printing\Printer\Printer;
use Rialto\Sales\Invoice\SalesInvoice;
use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Shipping\Export\Document\ElectronicExportInformation;

class SalesPrintManager
{
    /**
     * The service ID of the printer that should print the documents.
     */
    const PRINTER_ID = 'standard';

    /** @var SalesPdfGenerator */
    private $pdfGenerator;

    /** @var PrintQueue */
    private $printQueue;

    /** @var Printer */
    private $printer;

    public function __construct(SalesPdfGenerator $pdfGenerator,
                                PrintQueue $printQueue,
                                string $printerId)
    {
        $this->pdfGenerator = $pdfGenerator;
        $this->printQueue = $printQueue;
        $this->printer = $this->printQueue->getPrinter($printerId);
    }

    public function queueDebtorTransaction(DebtorTransaction $transaction, $numCopies = 1)
    {
        $pdf = $this->pdfGenerator->generateDebtorTransactionPdf($transaction);
        $job = PrintJob::pdf($pdf, $numCopies);
        $job->setDescription($transaction);
        $this->printQueue->add($job, self::PRINTER_ID);
    }

    public function queueCommercialInvoice(SalesInvoice $invoice, $numCopies = 1)
    {
        $pdf = $this->pdfGenerator->generateCommercialInvoicePdf($invoice);
        $job = PrintJob::pdf($pdf, $numCopies);
        $job->setDescription($invoice);
        $this->printQueue->add($job, self::PRINTER_ID);

    }

    public function queueEei(ElectronicExportInformation $eei, $numCopies = 3)
    {
        $pdf = $this->pdfGenerator->generateSedFormPdf($eei);
        $job = PrintJob::pdf($pdf, $numCopies);
        $job->setDescription($eei);
        $this->printQueue->add($job, self::PRINTER_ID);
    }

    public function queuePackingSlip(SalesOrderInterface $order)
    {
        $job = $this->makePackingSlipJob($order);
        $this->printQueue->add($job, self::PRINTER_ID);
    }

    private function makePackingSlipJob(SalesOrderInterface $order)
    {
        $pdf = $this->pdfGenerator->generatePackingSlip($order);
        $job = PrintJob::pdf($pdf);
        $job->setDescription(sprintf(
            'Packing slip for sales order %s',
            $order->getOrderNumber()));
        return $job;
    }

    public function printPackingSlip(SalesOrderInterface $order)
    {
        $job = $this->makePackingSlipJob($order);
        $this->printer->printJob($job);
    }
}
