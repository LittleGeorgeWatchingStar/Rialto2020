<?php

namespace Rialto\Printing\Printer;

use Rialto\Printing\Job\PrintJob;

/**
 * For printing regular pages.
 */
class StandardPrinter extends Printer
{
    /**
     * @var string
     */
    const LOCKFILE_NAME = '/tmp/gumstix_standard_printer_lockfile';

    protected function getLockfileName()
    {
        return self::LOCKFILE_NAME;
    }

    public function printJob(PrintJob $job)
    {
        $ps = $this->getRawData($job);
        for ($i = 0; $i < $job->getNumCopies(); $i++) {
            $this->printString($ps);
        }
    }

    public function printString(string $data)
    {
        $this->open();
        $this->write($data);
        $this->close();
    }
}
