<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Rialto\Purchasing\Supplier\Supplier;

/**
 * Attempts to download supplier invoices from links in the email body.
 */
class AttachmentLocatorBodyLink implements AttachmentLocatorStrategy
{
    /** @var Client */
    private $http;

    /** @var SupplierInvoiceFilesystem */
    private $filesystem;

    public function __construct(
        Client $http,
        SupplierInvoiceFilesystem $fs)
    {
        $this->http = $http;
        $this->filesystem = $fs;
    }

    /** @return string */
    public function getLocation()
    {
        return SupplierInvoicePattern::LOCATION_BODY_LINK;
    }

    /**
     * Finds the invoices in the email message, saves them to the hard disk,
     * and attaches them to the email.
     */
    public function loadAttachments(SupplierEmail $email)
    {
        $message = $email->getMessage();
        $body = $message->getContent();
        $urls = $this->extractUrls($body);
        foreach ($urls as $urlNum => $url) {
            if ($this->isValid($url)) {
                $file = $this->downloadFile($email->getSupplier(), $url);
                if ($file) {
                    $email->addAttachment($urlNum, $file);
                }
            }
        }
    }

    /**
     * @param string $body
     * @return string[]
     */
    private function extractUrls($body)
    {
        $pattern = '@https?://[^\'"\s]+@';
        $matches = [];
        preg_match($pattern, $body, $matches);
        return $matches;
    }

    /** @return bool */
    private function isValid($url)
    {
        $resp = $this->http->head($url, ['http_errors' => false]);
        return $this->isSuccessful($resp)
            && $this->isSmallEnough($resp);
    }

    private function isSuccessful(ResponseInterface $resp)
    {
        $status = $resp->getStatusCode();
        return $status >= 200 && $status < 300;
    }

    private function isSmallEnough(ResponseInterface $resp)
    {
        $header = $resp->getHeader('content-length');
        return isset($header[0])
            && $header[0] < static::MAX_ATTACHMENT_SIZE;
    }

    private function downloadFile(Supplier $supplier, $url)
    {
        $resp = $this->http->get($url);
        $filedata = $resp->getBody();
        if ($filedata) {
            $filename = basename($url);
            return $this->filesystem->saveInvoice($supplier, $filename, $filedata);
        }
        return null;
    }
}
