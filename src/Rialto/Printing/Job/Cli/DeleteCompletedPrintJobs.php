<?php

namespace Rialto\Printing\Job\Cli;


use Rialto\Printing\Job\PrintQueue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCompletedPrintJobs extends Command
{
    /** @var PrintQueue */
    private $queue;

    public function __construct(PrintQueue $queue)
    {
        parent::__construct();
        $this->queue = $queue;
    }

    protected function configure()
    {
        $this->setName('print:delete-jobs')
            ->setAliases(['rialto:print:delete'])
            ->setDescription("Delete completed print jobs")
            ->addOption('before', null, InputOption::VALUE_OPTIONAL,
                'Delete jobs printed before this date');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $before = $input->getOption('before');
        $before = $before ? new \DateTime($before) : null;
        $numDeleted = $this->queue->deleteOldJobs($before);
        $output->writeln("Deleted $numDeleted old print jobs.");
    }
}
