<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use RecursiveIteratorIterator;
use Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Rialto\Purchasing\Supplier\Supplier;
use Zend\Mail\Storage\Part\PartInterface;
use Zend\Mime\Mime;

/**
 * Gets the invoice from an attachment.
 */
class AttachmentLocatorAttachment implements AttachmentLocatorStrategy
{
    /** @var SupplierInvoiceFilesystem */
    private $filesystem;

    public function __construct(SupplierInvoiceFilesystem $fs)
    {
        $this->filesystem = $fs;
    }

    public function getLocation()
    {
        return SupplierInvoicePattern::LOCATION_ATTACHMENT;
    }

    public function loadAttachments(SupplierEmail $email)
    {
        $message = $email->getMessage();
        if (! $message->isMultipart()) {
            return;
        }
        foreach (new RecursiveIteratorIterator($message) as $partNum => $part) {
            /* @var $part PartInterface */
            if ($part->contentType == 'text/plain') {
                continue;
            }
            if ($part->getSize() > static::MAX_ATTACHMENT_SIZE) {
                $att = $email->addAttachment($partNum, new \SplFileInfo('/dev/null'));
                $att->addError(new \RuntimeException(
                    "Part $partNum is too big: " . number_format($part->getSize())
                ));
                continue;
            }
            $filename = $this->getAttachmentFilename($part);
            if (! $filename) {
                continue;
            }
            $filename = $this->getTargetFilename($email, $filename);
            $file = $this->saveAttachment($email->getSupplier(), $part, $filename);
            $email->addAttachment($partNum, $file);
        }
    }


    private function getAttachmentFilename(PartInterface $part)
    {
        $headersToCheck = [
            'content-type' => [
                '/name="([^"]+)"/',
            ],
            'content-disposition' => [
                '/filename="([^"]+)/',
                '/filename=([^" ]+)/',
            ]
        ];
        foreach ($headersToCheck as $header => $patterns) {
            if (! $part->getHeaders()->has($header)) {
                continue;
            }
            $headerValue = $part->getHeader($header)->toString();
            foreach ($patterns as $pattern) {
                $matches = [];
                if (preg_match($pattern, $headerValue, $matches)) {
                    return $matches[1];
                }
            }
        }
        return null;
    }

    /**
     * @return string
     *  The filename to which we want to save the invoice.
     */
    private function getTargetFilename(SupplierEmail $email,
                                       string $attachmentName): string
    {
        $extension = pathinfo($attachmentName, PATHINFO_EXTENSION);
        $basename = pathinfo($attachmentName, PATHINFO_FILENAME);
        $basename = $email->getSubject() . $email->getDateString() . $basename;
        $basename = preg_replace('/[^a-zA-Z0-9_]/', '', $basename);
        return "$basename.$extension";
    }

    /** @return \SplFileInfo */
    private function saveAttachment(Supplier $supplier,
                                    PartInterface $part,
                                    string $filename)
    {
        $filedata = $this->getDecodedContent($part);
        return $this->filesystem->saveInvoice($supplier, $filename, $filedata);
    }

    private function getDecodedContent(PartInterface $part)
    {
        $encoded = $part->getContent();
        if (! $part->getHeaders()->has('content-transfer-encoding')) {
            return $encoded;
        }
        $encoding = strtolower($part->getHeader('content-transfer-encoding', 'string'));
        switch ($encoding) {
            case Mime::ENCODING_BASE64:
                return imap_base64($encoded);

            case Mime::ENCODING_QUOTEDPRINTABLE:
                return imap_qprint($encoded);

            case Mime::ENCODING_7BIT:
                return quoted_printable_decode($encoded);

            default:
                throw new \UnexpectedValueException("Unknown encoding type $encoding");
        }
    }
}
