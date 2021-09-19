<?php

namespace Rialto\ManufacturingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use Rialto\ManufacturingBundle\Entity\WorkOrder;
use Rialto\ManufacturingBundle\Entity\WorkOrderIssue;
use Rialto\StockBundle\Repository\StandardCostRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 *
 */
class PopulateIssueStandardCostCommand
extends ContainerAwareCommand
{
    const BATCH_SIZE = 200;

    /** @var EntityManager */
    private $em;

    /** @var StandardCostRepository */
    private $repo;

    /** @var OutputInterface */
    private $out;

    protected function configure()
    {
        $this->setName('rialto:populate-issue-standard-cost')
            ->setDescription('Populate the standard cost field of work order issue items')
            ->addArgument('batchSize', InputArgument::OPTIONAL,
                'Number of records to process at a time',
                self::BATCH_SIZE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->out = $output;
        $container = $this->getContainer();
        $doctrine = $container->get('doctrine');
        $this->em = $doctrine->getEntityManager();
        $this->repo = $doctrine->getRepository('RialtoStockBundle:StandardCost');

        $batchSize = (int) $input->getArgument('batchSize');
        $sql = "select distinct wo.*
            from WorksOrders wo
            join WOIssues i on i.WorkOrderID = wo.WORef
            left join WOIssueItems ii
            on ii.IssueID = i.IssueNo
            and ii.UnitStandardCost > 0
            where wo.UnitsIssued > 0
            and wo.WORef > 20000
            and ii.ID is null
            order by wo.WORef desc
            limit $batchSize";

        $rsm = new ResultSetMappingBuilder($this->em);
        $rsm->addRootEntityFromClassMetadata('Rialto\ManufacturingBundle\Entity\WorkOrder', 'wo');
        $query = $this->em->createNativeQuery($sql, $rsm);

        $this->out->writeln('Order  ______Stored  __Calculated  __Difference  _Error');

        foreach ( $query->getResult() as $wo ) {
            $this->processOrder($wo);
        }
        $this->em->flush();
    }

    private function processOrder(WorkOrder $wo)
    {
        $calculated = 0;
        foreach ( $wo->getIssues() as $issue ) {
            $this->processIssue($issue);
            $calculated += $issue->getTotalValueIssued();
        }
        $calculated = round($calculated, 4);
        $stored = round($wo->getTotalValueIssued(), 4);
        $diff = $stored - $calculated;
        $error = $stored != 0 ? abs($diff / $stored) : INF;
        $msg = sprintf('%s  %12s  %12s  %12s  %6s',
            $wo->getId(),
            number_format($stored, 4),
            number_format($calculated, 4),
            number_format($diff, 4),
            number_format($error, 2));
        if ( $error >= 1.0 ) {
            $msg = "<error>$msg</error>";
        }
        elseif ( $error > 0 ) {
            $msg = "<comment>$msg</comment>";
        }
        $this->out->writeln($msg);
    }

    private function processIssue(WorkOrderIssue $issue)
    {
        $issueDate = $issue->getDateIssued();
        foreach ( $issue->getIssuedItems() as $item ) {
            if ( $item->getUnitStandardCost() > 0 ) continue;
            $stockItem = $item->getStockItem();
            $stdCost = $this->repo->findByItemAndDate($item, $issueDate);
            $amount = $stdCost ? $stdCost->getTotalCost() : $stockItem->getStandardCost();
            $item->setUnitStandardCost($amount);
        }
    }
}
