<?php

namespace Rialto\Sales\Order\Cli;

use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrderDetail;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FinalPriceCommand extends ContainerAwareCommand
{
    /** @var DbManager */
    private $om;

    /** @var SalesOrderRepository */
    private $repo;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('sales:final-price')
            ->setDescription("Recalculate sales order detail final unit prices");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->om = $this->getContainer()->get(DbManager::class);
        $this->repo = $this->om->getRepository(SalesOrderDetail::class);
        $query = $this->repo->createQueryBuilder('item')
            ->andWhere('item.finalUnitPrice is null')
            ->getQuery();
        $limit = 1000;
        $total = 0;
        do {
            /** @var $items SalesOrderDetail[] */
            $items = $query
                ->setMaxResults($limit)
                ->getResult();
            $this->om->beginTransaction();
            try {
                foreach ($items as $item) {
                    // Calling getFinalUnitPrice() will do the initial calculation.
                    $io->writeln(sprintf("%40s : %8.2f", $item, $item->getFinalUnitPrice()));
                }
                $this->om->flushAndCommit();
                $this->om->clear();
            } catch (\Exception $ex) {
                $this->om->rollBack();
                throw $ex;
            }
            $total += count($items);
            $io->note("Updated $total items in total.");
        } while (count($items) > 0);
    }

}
