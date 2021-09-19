<?php

namespace Rialto\Printing\Printer;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Filetype\Postscript\PostscriptConverter;
use Rialto\Printing\Job\PrintJob;
use Rialto\Util\Lock\BlockingSemaphore;
use Rialto\Util\Lock\FileSemaphore;
use Rialto\Util\Lock\Semaphore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Rialto can send print jobs to various printers located throughout the
 * organization.
 */
abstract class Printer
{
    /**
     * The maximum number of times that this will attempt to acquire an
     * exclusive lock on the printer lockfile.
     *
     * @var int
     */
    const MAX_LOCK_ATTEMPTS = 5;

    /**
     * How many seconds to wait for a connection before giving up.
     *
     * @var int
     */
    const CONNECTION_TIMEOUT = 5;

    /** @var string */
    private $id;

    /**
     * A human-friendly description of this printer.
     *
     * @var string
     * @Assert\Length(max=255)
     */
    private $description = '';

    /** @var Semaphore */
    private $lock;

    /** @var PrinterSocket */
    private $socket;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     */
    private $host;

    /**
     * @var int
     * @Assert\NotNull
     * @Assert\Range(min=1, max=65536)
     */
    private $port;

    /**
     * @deprecated Use @see PrintServer instead
     * @return Printer
     */
    public static function get($printerId, ObjectManager $om)
    {
        $printer = $om->find(self::class, $printerId);
        assertion(null !== $printer);
        return $printer;
    }

    /**
     * If the printer was not manually closed, the destructor will close it.
     */
    public function __destruct()
    {
        try {
            if (!$this->isClosed()) {
                $this->close();
            }
        } catch (\Exception $ex) {
            error_log($ex->getMessage() . PHP_EOL . $ex->getTraceAsString());
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return sprintf('%s printer on %s:%d',
            $this->id,
            $this->host,
            $this->port);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = trim($description);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = trim($host);
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = (int) $port;
    }

    /**
     * Override the default socket. Useful for testing.
     */
    public function setSocket(PrinterSocket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @return string
     *  The full path to this printer's lockfile.
     */
    protected abstract function getLockfileName();

    /**
     * Returns true if the connection to the printer port is fully established.
     * Note that it is possible for the connection to be partially established,
     * in which case both this method and isClosed() will return false!
     *
     * @see isClosed()
     * @return bool
     */
    private function isOpen()
    {
        return $this->socket && $this->socket->isOpen();
    }

    /**
     * Returns true if the connection to the printer port is fully closed.
     * Note that it is possible for the connection to be partially closed,
     * in which case both this method and isOpen() will return false!
     *
     * @see isOpen()
     * @return bool
     */
    private function isClosed()
    {
        return $this->socket ? $this->socket->isClosed() : true;
    }

    public abstract function printJob(PrintJob $job);

    public abstract function printString(string $data);

    protected function getRawData(PrintJob $job)
    {
        switch ($job->getContentType()) {
            case PrintJob::FORMAT_PDF:
                $converter = new PostscriptConverter();
                return $converter->convertPdf($job->getData());
            default:
                return $job->getData();
        }
    }

    /**
     * Established a connection to the printer port.
     *
     * @throws PrinterException If a connection cannot be established.
     */
    public function open()
    {
        if ($this->isOpen()) {
            throw new PrinterException(sprintf('%s is already open',
                get_class($this)
            ));
        }
        $this->acquireLock();
        if (!$this->socket) {
            $this->socket = $this->defaultSocket();
        }
        $this->socket->open($this->host, $this->port);
    }

    private function acquireLock()
    {
        if (!$this->lock) {
            $this->lock = $this->defaultLock();
        }
        if (!$this->lock->acquire()) {
            throw new PrinterException(sprintf(
                "Unable to acquire a lock for %s", get_class($this)
            ));
        }
    }

    private function defaultLock()
    {
        return new BlockingSemaphore(
            new FileSemaphore($this->getLockfileName()),
            self::MAX_LOCK_ATTEMPTS);
    }

    private function defaultSocket()
    {
        return new PrinterSocket();
    }

    /**
     * Closes the connection to the printer port.
     */
    public function close()
    {
        $this->socket->close();
        $this->lock->release();
    }

    /**
     * Writes the given string, followed by a newline, to the output stream.
     * @param string $text
     * @throws PrinterException
     *  If this printer is not open yet.
     */
    protected function writeLine($text)
    {
        $this->write("$text\n");
    }

    /**
     * Writes the given string to the output stream.
     * @param string $text
     * @throws PrinterException
     *  If this printer is not open yet.
     */
    protected function write($text)
    {
        $this->socket->write($text);
    }
}
