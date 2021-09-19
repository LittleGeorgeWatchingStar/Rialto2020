<?php

namespace Rialto\Allocation\Cli;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Allocation\Allocation\BinAllocation;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Manufacturing\Allocation\Orm\StockAllocationRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeleteStockBinAllocationsCommand extends Command
{
    const NAME = 'allocation:delete-stock';

    /**
     * @var EntityManagerInterface
     */
    private $dbm;

    /**
     * @var StockAllocationRepository
     */
    private $repo;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct(self::NAME);
        $this->dbm = $em;
        $this->repo = $em->getRepository(StockAllocation::class);
    }

    protected function configure()
    {
        $this->addArgument('purchaseOrder', InputArgument::REQUIRED,
            'The ID of the purchase order');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $poId = $input->getArgument('purchaseOrder');

        /** @var $po PurchaseOrder|null */
        $po = $this->dbm->find(PurchaseOrder::class, $poId);
        if (!$po) {
            $output->writeln("No such PO $poId; it may have been deleted.");
            return;
        }

        foreach ($po->getWorkOrders() as $workOrder) {
            $requirements = $workOrder->getRequirements();
            $allocations = $this->repo->findBy([
                'requirement' => $requirements,
            ]);
            foreach ($allocations as $allocation) {
                if ($allocation instanceof BinAllocation) {
                    $output->writeln("Deleting allocation \"{$allocation->getId()}.\"");
                    $allocation->close();
                }
            }
        }

        $this->dbm->flush();

        $output->writeln("Successfully deleted allocations for PO\"$poId\"");
    }
}
