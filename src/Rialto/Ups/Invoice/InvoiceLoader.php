<?php

namespace Rialto\Ups\Invoice;

use Gaufrette\Adapter\PhpseclibSftp;
use Gaufrette\Filesystem;
use phpseclib\Net\SFTP;

/**
 * Fetches invoices from the UPS FTP server.
 */
class InvoiceLoader
{
    /** @var Filesystem */
    private $ftp;

    /** @var InvoiceParser[] */
    private $parsers = [];

    public function __construct($host, $username, $password)
    {
        $sftp = new SFTP($host, $port = 10022);
        $success = $sftp->login($username, $password);
        if (!$success) {
            $reason = $sftp->getLastSFTPError() ?: 'unknown reason';
            throw new InvoiceLoaderException("Unable to SFTP into $username@$host port $port: $reason");
        }

        $adapter = new PhpseclibSftp($sftp,
            $remoteDir = null,
            $createDir = false);
        $this->ftp = new Filesystem($adapter);
    }

    /**
     * Add a parser for parsing different invoice formats (XML, CSV).
     */
    public function registerParser(InvoiceParser $parser)
    {
        $this->parsers[] = $parser;
    }

    /**
     * @return string[] Invoice files on the FTP server.
     */
    public function listFiles()
    {
        $allFiles = $this->ftp->listKeys()['keys'];
        return array_filter($allFiles, function ($filename) {
            return null !== $this->getParserOrNull($filename);
        });
    }

    /** @return InvoiceParser|null */
    private function getParserOrNull($filename)
    {
        foreach ($this->parsers as $parser) {
            if ($parser->canHandleFile($filename)) {
                return $parser;
            }
        }
        return null;
    }

    /** @return InvoiceParser */
    public function getParser($filename)
    {
        $parser = $this->getParserOrNull($filename);
        if ($parser) {
            return $parser;
        }
        throw new \InvalidArgumentException("No parser available to handle '$filename'");
    }

    /**
     * @param string $filename
     * @return string The contents the file.
     */
    public function getInvoice($filename)
    {
        $file = $this->ftp->get((string) $filename);
        return $file->getContent();
    }
}
