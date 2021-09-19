<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use Gumstix\Filetype\CsvFile;
use Rialto\Filetype\Excel\XlsConverter;
use Rialto\Filetype\Image\OcrConverter;
use Rialto\Filetype\Pdf\PdfConverter;
use Rialto\Purchasing\Invoice\Parser\SupplierInvoiceParser;
use Rialto\Purchasing\Invoice\SupplierEmailAttachment;
use Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Rialto\Purchasing\Supplier\Supplier;
use SplFileInfo;
use UnexpectedValueException;

/**
 * Converts attachments from supplier emails from whatever format they're in
 * to a CSV file that can be parsed by SupplierInvoiceParser.
 *
 * @see SupplierInvoiceParser
 */
class AttachmentConverter
{
    /** @var PdfConverter */
    private $pdf;

    /** @var OcrConverter */
    private $ocr;

    /** @var XlsConverter */
    private $xls;

    /** @var SupplierInvoiceFilesystem */
    private $filesystem;

    public function __construct(
        PdfConverter $pdfConverter,
        OcrConverter $ocrConverter,
        SupplierInvoiceFilesystem $filesystem)
    {
        $this->pdf = $pdfConverter;
        $this->ocr = $ocrConverter;
        $this->xls = new XlsConverter(); // add via dependency injection when needed
        $this->filesystem = $filesystem;
    }

    public function convertAttachments(SupplierEmail $email)
    {
        foreach ( $email->getAttachments() as $attachment ) {
            $this->convertAttachment($email, $attachment);
        }
    }

    private function convertAttachment(SupplierEmail $email, SupplierEmailAttachment $attachment)
    {
        $pattern = $email->getPattern();
        $original = $attachment->getOriginal();
        if (! $pattern->isSupportedFiletype($original) ) {
            return;
        }
        $csv = $this->convert($original, $pattern);
        $attachment->setData($csv->toArray());

        $csvFile = $this->saveCsv($original, $csv, $email->getSupplier());
        $attachment->setCsv($csvFile);

    }

    /** @return CsvFile */
    private function convert(SplFileInfo $file, SupplierInvoicePattern $pattern)
    {
        $format = $pattern->getFormat();
        switch ( $format ) {
            case SupplierInvoicePattern::FORMAT_PDF:
            case SupplierInvoicePattern::FORMAT_OCR:
                $lines = $this->getLines($file, $format);
                return $this->splitLines($lines, $pattern->getSplitPattern());
            case SupplierInvoicePattern::FORMAT_XLS:
                return $this->xls->toCsvFile($file);
            default:
                throw new UnexpectedValueException("Unexpected format $format");
        }
    }

    /** @return string[] */
    private function getLines(SplFileInfo $file, $format)
    {
        switch ( $format ) {
            case SupplierInvoicePattern::FORMAT_PDF:
                return $this->pdf->toLines($file);
            case SupplierInvoicePattern::FORMAT_OCR:
                return $this->ocr->toLines($file);
            default:
                throw new UnexpectedValueException("Unexpected format $format");
        }
    }

    /**
     * Breaks the PDF into lines of text, and then splits those lines of text
     * according the the given regular expression.
     *
     * @param string $splitPattern
     *  The regular expression on which to split the lines.  The default
     *  value splits on two or more consecutive whitespace characters.
     * @return CsvFile
     */
    private function splitLines(array $lines, $splitPattern = '/\s{2,}/')
    {
        $grid = [];
        foreach ( $lines as $line ) {
            $clean = trim($line);
            $grid[] = preg_split($splitPattern, $clean);
        }

        $csvFile = new CsvFile();
        $csvFile->parseArray($grid);
        return $csvFile;
    }

    /** @return SplFileInfo */
    private function saveCsv(SplFileInfo $original, CsvFile $csv, Supplier $supplier)
    {
        $ext = $original->getExtension();
        $csvFilename = preg_replace("/\.$ext$/i", ".csv", $original->getBasename());
        return $this->filesystem->saveInvoice($supplier, $csvFilename, $csv->toString());
    }
}
