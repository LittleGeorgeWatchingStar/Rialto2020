<?php

namespace Rialto\Printing\Printer;

use Rialto\IllegalStateException;

/**
 * A network socket to connect to a remote printer.
 */
class PrinterSocket
{
    const CONNECTION_TIMEOUT = 5;

    private $stream = null;
    private $errorNo = null;
    private $errorStr = null;

    public function open($host, $port)
    {
        $this->errorNo = null;
        $this->errorStr = null;

        $this->stream = @fsockopen(
            $host,
            $port,
            $this->errorNo,
            $this->errorStr,
            self::CONNECTION_TIMEOUT
        );
        if (!$this->stream) throw new PrinterException(sprintf(
            'Error %s opening printer socket to %s:%s: %s',
            $this->errorNo, $host, $port, $this->errorStr
        ));
    }

    public function isOpen()
    {
        return (bool) $this->stream;
    }

    public function close()
    {
        if (is_resource($this->stream)) fclose($this->stream);
        $this->stream = null;
    }

    public function isClosed()
    {
        return (!$this->stream);
    }

    /**
     * Writes the given string to the open socket.
     * @param string $text
     * @return int
     *  The number of bytes written.
     * @throws IllegalStateException
     *  If this printer is not open yet.
     * @throws PrinterException
     *  If there is an error writing the string.
     */
    public function write($text)
    {
        if (!$this->isOpen()) throw new IllegalStateException(
            'printer socket has not been opened yet'
        );
        $toWrite = strlen($text);
        $fwrite = 0;
        for ($written = 0; $written < $toWrite; $written += $fwrite) {
            $fwrite = @fwrite($this->stream, substr($text, $written));
            if (false === $fwrite) {
                $diff = $toWrite - $written;
                throw new PrinterException(sprintf(
                    'Unable to write %s of %s bytes to %s',
                    $diff, $toWrite, $this->getSocketUri()
                ));
            }
        }
        return $written;
    }

    private function getSocketUri()
    {
        if (!$this->isOpen()) return '';
        $data = stream_get_meta_data($this->stream);
        return $data['uri'];
    }
}
