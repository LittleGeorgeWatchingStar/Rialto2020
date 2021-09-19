<?php

namespace Rialto\Printing\Job\Cli;

use Rialto\Database\Orm\DbManager;
use Rialto\Printing\Job\PrintJob;
use Rialto\Printing\Job\PrintQueue;
use Rialto\Printing\Printer\PrinterException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prints any unprinted jobs in the print queue.
 */
class FlushPrintQueue extends Command
{
    /** Maximum number of jobs to print per execution. */
    const DEFAULT_MAX_JOBS = 20;

    /** @var OutputInterface */
    private $output;

    /** @var DbManager */
    private $dbm;

    /** @var PrintQueue */
    private $queue;

    public function __construct(DbManager $dbm, PrintQueue $queue)
    {
        parent::__construct();
        $this->dbm = $dbm;
        $this->queue = $queue;
    }

    protected function configure()
    {
        $this->setName('print:flush')
            ->setAliases(['rialto:print:flush'])
            ->setDescription('Prints any unprinted jobs in the print queue')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL,
                'Max number of jobs to print.', self::DEFAULT_MAX_JOBS);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $start = microtime(true);
        $this->debug(sprintf("Start : %18.2f", $start));

        $jobs = $this->queue->getOutstandingJobs($input->getOption('limit'));
        foreach ($jobs as $job) {
            $this->printJob($job);
        }
        $end = microtime(true);
        $this->debug(sprintf("End   : %18.2f", $end));
        $this->debug(sprintf("Total : %18.2f", $end - $start));
    }

    private function printJob(PrintJob $job)
    {
        $this->dbm->beginTransaction();
        try {
            $printer = $job->getPrinter();
            $printer->printJob($job);
            $job->setPrinted();
            $this->dbm->flushAndCommit();
            $this->debug("Printed $job successfully.");
        } catch (PrinterException $ex) {
            $this->dbm->rollBack();
            /* In this case, we'll just try again next time. */
            $error = $ex->getMessage();
            $this->output->writeln("<info>$error</info>");
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            /* This is a more serious error that won't go away on its own. */
            $error = $ex->getMessage();
            $job->setError($error);
            $this->dbm->flush();

            error_log($error . $ex->getTraceAsString());
            $this->output->writeln("<error>$error</error>");
        }
    }

    private function debug($text)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln($text);
        }
    }
}
