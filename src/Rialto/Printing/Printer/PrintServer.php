<?php


namespace Rialto\Printing\Printer;


use Rialto\Printing\Job\PrintJob;

/**
 * Provides other services with access to printers.
 */
interface PrintServer
{
    /**
     * @return Printer The printer whose ID is given
     */
    public function getPrinter(string $printerId): Printer;

    /**
     * Print raw $data string to the printer whose ID is given.
     */
    public function printString(string $printerId, string $data);

    /**
     * Print $job to the printer whose ID is given.
     */
    public function printJob(string $printerId, PrintJob $job);
}
