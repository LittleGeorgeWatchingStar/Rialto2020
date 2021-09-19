<?php

namespace Rialto\Stock\Item\Cli;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Rialto\Port\CommandBus\CommandQueue;
use Rialto\Stock\Item\Command\RefreshStockLevelCommand;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StockLevelRefreshCommand extends Command
{
    const NAME = 'stock-item:update-stock-level';

    /** @var EntityManagerInterface */
    private $em;

    /** @var StockItemRepository */
    private $stockItemRepo;

    /** @var CommandQueue */
    private $commandQueue;

    public function __construct(EntityManagerInterface $em, CommandQueue $commandQueue)
    {
        parent::__construct(self::NAME);
        $this->em = $em;
        $this->stockItemRepo = $this->em->getRepository(StockItem::class);
        $this->commandQueue = $commandQueue;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $activeStockItems = $this->stockItemRepo->findActiveProducts();

        foreach ($activeStockItems as $stockItem) {
            try {
                $command = new RefreshStockLevelCommand($stockItem->getSku());
                $this->commandQueue->queue($command, false);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        $this->em->flush();

        return 0;
    }
}
