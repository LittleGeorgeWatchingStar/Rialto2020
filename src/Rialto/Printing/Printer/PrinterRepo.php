<?php

namespace Rialto\Printing\Printer;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnexpectedResultException;
use Rialto\Printing\Job\PrintJob;

class PrinterRepo extends EntityRepository implements PrintServer
{
    public function getPrinter(string $printerId): Printer
    {
        /** @var Printer $printer */
        $printer = $this->find($printerId);
        if ($printer) {
            return $printer;
        }
        throw new UnexpectedResultException("No such printer '$printerId'");
    }

    public function printString(string $printerId, string $data)
    {
        $printer = $this->getPrinter($printerId);
        $printer->printString($data);
    }

    public function printJob(string $printerId, PrintJob $job)
    {
        $printer = $this->getPrinter($printerId);
        $printer->printJob($job);
    }
}
