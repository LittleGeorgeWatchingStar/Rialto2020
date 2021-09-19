<?php

namespace Rialto\Purchasing\Invoice;

use Gumstix\Storage\FileStorage;
use Rialto\Filesystem\Filesystem;
use Rialto\Filesystem\FilesystemException;
use Rialto\Filetype\ZipFile;
use Rialto\Purchasing\Supplier\Supplier;
use SplFileInfo;


/**
 * Manages the filesystems where supplier invoices are processed and saved.
 *
 * This class manages two filesystems: a local temporary one for manipulating
 * the invoice files, and a permanent one (which may be remote) for storing
 * the original and processed invoice files.
 */
class SupplierInvoiceFilesystem extends Filesystem
{
    const TEMP_DIR = 'temp4zip';

    /**
     * Permanently stores original and parsed supplier invoices.
     * @var FileStorage
     */
    private $permanentStorage;

    public function __construct(FileStorage $permanentStorage)
    {
        parent::__construct(sys_get_temp_dir());
        $this->permanentStorage = $permanentStorage;
    }

    /** @return SplFileInfo */
    public function saveInvoice(Supplier $supplier, $filename, $filedata)
    {
        $filepath = $this->getFilepath($supplier, $filename);
        $this->write($filepath, $filedata);
        $key = $this->getPermanentKey($supplier, $filename);
        $this->permanentStorage->put($key, $filedata);
        return $filepath;
    }

    /** @return SplFileInfo */
    public function getFilepath(Supplier $supplier, $filename)
    {
        $dirname = $this->getInvoiceDirectory($supplier);
        return $this->join($dirname, $filename);
    }

    /** @return SplFileInfo */
    private function getInvoiceDirectory(Supplier $supplier)
    {
        $path = $this->getDirectoryPath($supplier);
        $workingDir = $this->join($this->rootDir, $path);
        if ( ! $workingDir->isDir() ) {
            $this->mkdir($workingDir);
        }
        return $workingDir;
    }

    /** @return SplFileInfo */
    private function getDirectoryPath(Supplier $supplier)
    {
        assertion($supplier->getId(), "$supplier has no ID");
        return $this->join('purchasing', 'invoices', $supplier->getId());
    }

    /** @return string */
    private function getPermanentKey(Supplier $supplier, $filename)
    {
        return (string) $this->join($this->getDirectoryPath($supplier), $filename);
    }

    /**
     * True if the original invoice exists in the permanent file storage.
     * @param SupplierInvoice $invoice
     * @return bool
     */
    public function fileExistsForInvoice(SupplierInvoice $invoice)
    {
        if (! $invoice->getFilename() ) {
            return false;
        }
        $key = $this->getPermanentKey($invoice->getSupplier(), $invoice->getFilename());
        return $this->permanentStorage->exists($key);
    }

    /**
     * Returns the contents of the given invoice file.
     */
    public function getFileContents(Supplier $supplier, string $filename): string
    {
        $key = $this->getPermanentKey($supplier, $filename);
        return $this->permanentStorage->get($key);
    }

    /** @return SplFileInfo */
    public function extractAndSaveInvoice(Supplier $supplier, $filename, $zipData)
    {
        $tempfile = $this->saveTempFile($zipData);
        $invoiceDir = $this->getInvoiceDirectory($supplier);
        $zip = new ZipFile();
        $zip->open($tempfile);
        $zip->extractTo($invoiceDir);
        $zip->close();
        unlink($tempfile);

        $filepath = $this->join($invoiceDir, $filename);
        if (! $filepath->isFile() ) {
            throw new FilesystemException($filepath, "Expected file not found");
        }
        return $filepath;
    }

    /** @return SplFileInfo */
    private function saveTempFile($filedata)
    {
        $filepath = $this->tempFile($this->getTempDirectory(), 'invoice');
        $this->write($filepath, $filedata);
        return $filepath;
    }

    /** @return SplFileInfo */
    private function getTempDirectory()
    {
        $tempDir = $this->join($this->rootDir, self::TEMP_DIR);
        if ( ! $tempDir->isDir() ) {
            $this->mkdir($tempDir);
        }
        return $tempDir;
    }
}
