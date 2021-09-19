<?php


namespace Rialto\Purchasing\Invoice;


use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gumstix\Storage\GaufretteStorage;
use Rialto\Accounting\Supplier\PaymentRun;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Filesystem\TempFilesystem;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoiceRepository;

class SupplierInvoiceZipper
{
    /** @var TempFilesystem */
    private $tempFs;

    /** @var SupplierInvoiceFilesystem */
    private $invoiceFs;

    /** @var SupplierInvoiceRepository */
    private $invoiceRepo;

    public function __construct(TempFilesystem $tempFs,
                                SupplierInvoiceFilesystem $invoiceFs,
                                EntityManagerInterface $em)
    {
        $this->tempFs = $tempFs;
        $this->invoiceFs = $invoiceFs;
        $this->invoiceRepo = $em->getRepository(SupplierInvoice::class);
    }

    public function zipPaymentRun(PaymentRun $run): string
    {
        $zipFilePath = $this->getZipFilePath();
        $zipFile = $this->createZipFile($zipFilePath);

        $errors = [];
        foreach ($run->getInvoices() as $supplier) {
            foreach ($run->getInvoices()[$supplier] as $transaction) {
                if ($error = $this->addTransactionInvoicesToZip($transaction, $zipFile)) {
                    $errors[] = $error;
                }
            }
        }

        if ($errors) {
            $zipFile->put('errors.txt', implode(PHP_EOL, $errors));
        }

        return file_get_contents($zipFilePath);
    }

    private function getZipFilePath(): string
    {
        $filepath = $this->tempFs->getTempfile("invoices", 'zip');
        return $filepath;
    }

    private function createZipFile(string $zipFilePath): GaufretteStorage
    {
        $this->tempFs->remove($zipFilePath);
        return GaufretteStorage::zipfile($zipFilePath);
    }

    /**
     * @return string|null A string explaining the error if one occurred.
     */
    private function addTransactionInvoicesToZip(SupplierTransaction $transaction,
                                                 GaufretteStorage $zipFile)
    {
        if (!$transaction->isInvoice()) return null;
        try {
            $invoice = $this->invoiceRepo->findBySupplierReference(
                $transaction->getSupplier(),
                $transaction->getReference());

            if (!$invoice) {
                return "{$transaction->getSupplierName()} - {$transaction->getReference()} has no invoice";
            }

            if (!$invoice->getFilename()) {
                return "'$invoice' has no stored file";
            }

            if (!$this->invoiceFs->fileExistsForInvoice($invoice)) {
                return "File '{$invoice->getFilename()}' not found";
            }

            $content = $this->invoiceFs->getFileContents(
                $invoice->getSupplier(),
                $invoice->getFilename());
            $zipFile->put($invoice->getFilename(), $content);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return null;
    }
}
