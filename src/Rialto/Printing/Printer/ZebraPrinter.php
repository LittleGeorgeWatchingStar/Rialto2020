<?php

namespace Rialto\Printing\Printer;

use Rialto\Printing\Job\PrintJob;


/**
 * Controls the Zebra label printer.
 */
class ZebraPrinter extends Printer
{
    /** @var string */
    const LOCKFILE_NAME = '/tmp/gumstix_ups_printer_lockfile';

    /**
     * How long do we sleep for after a print job?
     * @var int
     */
    const SLEEP_TIME_AFTER_JOB = 5;

    private $sleepTime = self::SLEEP_TIME_AFTER_JOB;

    /**
     * @return int
     */
    public function getSleepTime()
    {
        return $this->sleepTime;
    }

    /**
     * Sets the time, in seconds, that the process waits after completing
     * a print job.
     *
     * @param int $time
     * @return ZebraPrinter
     *  Fluent interface
     */
    public function setSleepTime($time)
    {
        $this->sleepTime = $time;
        return $this;
    }

    protected function getLockfileName()
    {
        return self::LOCKFILE_NAME;
    }

    private function sleep()
    {
        sleep($this->sleepTime);
    }

    public function printJob(PrintJob $job)
    {
        $this->printString($this->getRawData($job));
    }

    public function printString(string $input)
    {
        $this->open();
        $this->writeLine($input);
        $this->close();
        $this->sleep();
    }
}
