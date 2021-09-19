<?php

namespace Rialto\Stock\Publication;

use Rialto\Printing\Job\PrintJob;
use Rialto\Printing\Job\PrintQueue;
use Rialto\Printing\Printer\Printer;

class PublicationPrintManager
{
    const PRINTER_ID = 'color';

    /**
     * @var PublicationFilesystem
     */
    private $pubFS;

    /**
     * @var PrintQueue
     */
    private $printQueue;

    /**
     * @var Printer
     */
    private $printer;

    public function __construct(PublicationFilesystem $pubFS,
                                PrintQueue $printQueue)
    {
        $this->pubFS = $pubFS;
        $this->printQueue = $printQueue;
        $this->printer = $this->printQueue->getPrinter(self::PRINTER_ID);
    }

    public function queue(UploadPublication $pub)
    {
        $job = $this->makePrintJob($pub);
        $this->printQueue->add($job, $this->printer->getId());
    }

    private function makePrintJob(UploadPublication $pub)
    {
        $pdf = $this->pubFS->getFileContents($pub);
        $job = PrintJob::pdf($pdf);
        $job->setDescription("Publication $pub");
        return $job;
    }

    public function printNow(UploadPublication $pub)
    {
        $job = $this->makePrintJob($pub);
        $this->printer->printJob($job);
    }
}
