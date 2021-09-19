<?php

namespace Rialto\Printing\Job;

use Rialto\Database\Orm\DbManager;
use Rialto\Printing\Printer\Printer;

/**
 * Keeps track of pending print jobs.
 */
class PrintQueue
{
    /** @var DbManager */
    private $dbm;

    /** @var PrintJobRepository */
    private $repo;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
        $this->repo = $dbm->getRepository(PrintJob::class);
    }

    /**
     * Queues up $job to be printed on the printer identified by $printerId.
     */
    public function add(PrintJob $job, $printerId)
    {
        $printer = $this->getPrinter($printerId);
        $job->setPrinter($printer);
        $this->dbm->persist($job);
    }

    /** @return Printer */
    public function getPrinter($printerId)
    {
        return Printer::get($printerId, $this->dbm);
    }

    /** @return PrintJob[] */
    public function getOutstandingJobs($limit = null)
    {
        return $this->repo->findOutstandingJobs($limit);
    }

    /**
     * @return int The number of jobs deleted.
     */
    public function deleteOldJobs(\DateTime $before = null)
    {
        return $this->repo->deleteOldJobs($before);
    }
}
