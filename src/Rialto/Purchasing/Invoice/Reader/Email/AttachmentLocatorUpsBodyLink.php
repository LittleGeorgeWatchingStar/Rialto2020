<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use Rialto\Database\Orm\DbManager;
use Rialto\Legacy\CurlHelper;
use Rialto\NetworkException;
use Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Rialto\Purchasing\Supplier\Supplier;

/**
 * Extracts the UPS invoice from a link in the email body.
 */
class AttachmentLocatorUpsBodyLink implements AttachmentLocatorStrategy
{
    const UNZIP_FATAL_ERROR = 3;

    /** @var SupplierInvoiceFilesystem */
    private $filesystem;

    /** @var CurlHelper */
    private $curl;

    /** @var DbManager */
    private $dbm;

    public function __construct(
        SupplierInvoiceFilesystem $fs,
        CurlHelper $curl,
        DbManager $dbm)
    {
        $this->filesystem = $fs;
        $this->curl = $curl;
        $this->dbm = $dbm;
    }

    public function getLocation()
    {
        return SupplierInvoicePattern::LOCATION_UPS_BODY;
    }

    public function loadAttachments(SupplierEmail $email)
    {
        $message = $email->getMessage();
        $body = $message->getContent();

        $lines = explode("\r", $body);
        foreach ( $lines as $line ) {
            $starting = strpos($line, 'https');
            if ( false === $starting ) continue;

            $the_link = substr($line, $starting);
            //logDebug($the_link, 'the link');
            try {
                $the_ups_page = $this->curl->fetch($the_link);
            }
            catch ( NetworkException $ex ) {
                continue; // go to next line.
            }
            //logDebug($the_ups_page, 'the ups page');
            $the_ups_lines = explode("\n", $the_ups_page);

            foreach ( $the_ups_lines as $a_line ) {
                $pattern = '/<a href="(.*1-pdf\.zip.*)">/';
                $matches = [];
                if (! preg_match($pattern, $a_line, $matches) ) continue;
                $fileUri = $matches[1];

                $supplier = $this->getCorrectSupplier($email->getSupplier(), $fileUri);
                $email->setSupplier($supplier);
                $email->setPattern($this->getPattern($supplier));

                $filenamePattern = '/([A-Z0-9\-]+)-1-pdf\.zip/';
                $matches = [];
                if (! preg_match($filenamePattern, $fileUri, $matches) ) {
                    throw new \UnexpectedValueException(
                        "URL $fileUri does not match expected filename pattern."
                    );
                }
                $filename = $matches[1] . '.pdf';
                $filepath = $this->filesystem->getFilepath($supplier, $filename);
                if (! $filepath->isFile() ) {
                    try {
                        $zipData = $this->curl->fetch('https://epackage1.ups.com' . $fileUri);
                    }
                    catch ( NetworkException $ex ) {
                        continue; // go to next line.
                    }
                    $filepath = $this->filesystem->extractAndSaveInvoice($supplier, $filename, $zipData);
                }
                $email->addAttachment(0, $filepath);
            }
        }
    }

    /** @return Supplier */
    private function getCorrectSupplier(Supplier $supplier, $fileUri)
    {
        if ( (19 == $supplier->getId()) &&
             (false === strpos($fileUri, '7Y284V')))
        {
            return $this->dbm->need(Supplier::class, 108);
        }
        return $supplier;
    }

    /** @return SupplierInvoicePattern */
    private function getPattern(Supplier $supplier)
    {
        return $this->dbm->getRepository(SupplierInvoicePattern::class)
            ->findOneBy([
                'supplier' => $supplier->getId(),
            ]);
    }
}
