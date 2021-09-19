<?php


namespace Rialto\Purchasing\Invoice\Command;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem;

/**
 * Handler for the manual uploading of a supplier invoice.
 *
 * @see UploadSupplierInvoiceFileCommand
 */
final class UploadSupplierInvoiceFileHandler
{
    /** @var SupplierInvoiceFilesystem */
    private $filesystem;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(SupplierInvoiceFilesystem $filesystem,
                                EntityManagerInterface $em)
    {
        $this->filesystem = $filesystem;
        $this->em = $em;
    }

    public function handle(UploadSupplierInvoiceFileCommand $command)
    {
        $invoice = $command->invoice;
        $fileinfo = $this->filesystem->saveInvoice(
            $invoice->getSupplier(),
            $command->filename,
            $command->filedata);

        $invoice->setFilename($fileinfo->getBasename());

        $this->em->flush();
    }
}
