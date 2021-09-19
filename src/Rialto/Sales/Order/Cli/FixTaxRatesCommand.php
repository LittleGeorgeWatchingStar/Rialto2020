<?php

namespace Rialto\Sales\Order\Cli;

use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Type\SalesType;
use Rialto\Tax\TaxExemption;
use Rialto\Tax\TaxLookup;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixTaxRatesCommand extends ContainerAwareCommand
{
    /** @var DbManager */
    private $dbm;

    /** @var SalesOrderRepository */
    private $repo;

    /** @var TaxLookup */
    private $lookup;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('sales:fix-tax-rates')
            ->setDescription("Fix missing tax rates for orders")
            ->addArgument('since', InputArgument::OPTIONAL, 'The year to search', $this->defaultSince())
            ->addOption('limit', null, InputArgument::OPTIONAL, 'Max results', null);
    }

    private function defaultSince()
    {
        return sprintf('%s-01-01', date('Y'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $since = $input->getArgument('since');
        $limit = $input->getOption('limit');

        $this->dbm = $this->getContainer()->get(DbManager::class);
        $this->repo = $this->dbm->getRepository(SalesOrder::class);
        $this->lookup = $this->getContainer()->get(TaxLookup::class);

        $qb = $this->repo->createQueryBuilder('o')
            ->join('o.shippingAddress', 'addr')
            ->join('o.customerBranch', 'br')
            ->join('br.customer', 'cust')
            ->andWhere('o.dateOrdered >= :since')
            ->setParameter('since', $since)
            ->andWhere('o.salesType = :online')
            ->setParameter('online', SalesType::ONLINE)
            ->andWhere('addr.stateCode in (:calif)')
            ->setParameter('calif', ['CA', 'California'])
            ->andWhere('cust.taxExemptionStatus not in (:exempt)')
            ->setParameter('exempt', TaxExemption::exemptStatuses());
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        /** @var $orders SalesOrder[] */
        $orders = $qb->getQuery()->getResult();
        $io->note(sprintf("Found %d orders since $since.", count($orders)));

        foreach ($orders as $order) {
            $before = $order->getTaxAmount();
            $this->lookup->updateTaxRates($order);
            $after = $order->getTaxAmount();
            $io->writeln(sprintf('%40s : %10s => %10s.',
                $order,
                number_format($before, 2),
                number_format($after, 2)
            ));
        }
        $this->dbm->flush();
    }

}
