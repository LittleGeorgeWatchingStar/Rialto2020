<?php

namespace Rialto\Stock\Level\Cli;


use Psr\Log\LoggerInterface;
use Rialto\Database\Orm\DbManager;
use Rialto\Logging\Cli\LoggingCommand;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Level\StockLevelSynchronizer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StockLevelSyncCommand extends LoggingCommand
{
    const NAME = 'stock:sync';

    const DEFAULT_LIMIT = 10;

    /** @var DbManager */
    private $dbm;

    /** @var StockLevelSynchronizer */
    private $sync;

    public function __construct(DbManager $dbm,
                                StockLevelSynchronizer $sync,
                                LoggerInterface $logger)
    {
        parent::__construct(self::NAME, $logger);
        $this->dbm = $dbm;
        $this->sync = $sync;
    }

    protected function configure()
    {
        $this->setAliases(['rialto:stock:sync'])
            ->setDescription("Synchronize stock levels with external applications")
            ->addArgument('location', InputArgument::OPTIONAL,
                'Stock location to synchronize (default: headquarters)',
                Facility::HEADQUARTERS_ID)
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL,
                'Limit number of items to sync',
                self::DEFAULT_LIMIT);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $facility = $this->getFacility($input);
        $limit = (int) $input->getOption('limit');

        $this->dbm->beginTransaction();
        try {
            $updates = $this->sync->loadUpdates($facility, $limit);
            foreach ($updates as $update) {
                $this->notice("Updated $update.");
                $warnings = $this->sync->synchronize($update);
                $this->logWarnings($warnings);
            }
            $warnings = $this->sync->syncAssemblies($updates, $facility);
            $this->logWarnings($warnings);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
        $this->notice(sprintf('Updated %s items.', number_format(count($updates))));
    }

    private function getFacility(InputInterface $input): Facility
    {
        $id = $input->getArgument('location');
        return $this->dbm->find(Facility::class, $id);
    }

    private function logWarnings(array $warnings)
    {
        foreach ($warnings as $warning) {
            $this->warning($warning);
        }
    }

}
