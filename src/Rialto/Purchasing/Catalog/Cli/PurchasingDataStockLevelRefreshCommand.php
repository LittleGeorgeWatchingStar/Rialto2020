<?php

namespace Rialto\Purchasing\Catalog\Cli;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Rialto\Port\CommandBus\CommandQueue;
use Rialto\Purchasing\Catalog\Command\RefreshPurchasingDataStockLevelCommand;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class PurchasingDataStockLevelRefreshCommand extends Command
{
    const NAME = 'purchasing-catalog:update-purchasing-data-stock-level';

    /** @var EntityManagerInterface */
    private $em;

    /** @var PurchasingDataRepository */
    private $purchasingDataRepo;

    /** @var CommandQueue */
    private $commandQueue;

    public function __construct(EntityManagerInterface $em, CommandQueue $commandQueue)
    {
        parent::__construct(self::NAME);

        $this->em = $em;
        $this->purchasingDataRepo = $this->em->getRepository(PurchasingData::class);
        $this->commandQueue = $commandQueue;
    }

    protected function configure()
    {
        $this
            ->setDescription('Auto refresh purchasing data stock level')
            ->addArgument('since', InputArgument::OPTIONAL, '', '-1 months')
            ->addOption('syncAll', null, InputOption::VALUE_NONE, 'Sync all data from the remote API.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $since = new \DateTime($input->getArgument('since'));
        $syncAll = $input->getOption('syncAll');

        /** @var PurchasingData[] $allPurchasingData */
        $allPurchasingData = $this->purchasingDataRepo->findAllActiveAncientPurchasingData($since);

        foreach ($allPurchasingData as $purchasingData) {
            try {
                $command = new RefreshPurchasingDataStockLevelCommand($purchasingData->getId(), !$syncAll);
                $this->commandQueue->queue($command, false);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        $this->em->flush();

        return 0;
    }
}
