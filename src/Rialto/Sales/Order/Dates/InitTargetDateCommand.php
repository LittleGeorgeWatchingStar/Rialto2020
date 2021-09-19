<?php

namespace Rialto\Sales\Order\Dates;


use Doctrine\Common\Persistence\ObjectManager;
use Gumstix\Time\DateRange;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Initialize the target ship dates for orders that don't have one.
 */
class InitTargetDateCommand extends Command
{
    /** @var ObjectManager */
    private $om;

    /** @var SalesOrderRepository */
    private $repo;

    /** @var TargetShipDateCalculator */
    private $calculator;

    public function __construct(ObjectManager $om, TargetShipDateCalculator $calculator)
    {
        parent::__construct();
        $this->om = $om;
        $this->repo = $om->getRepository(SalesOrder::class);
        $this->calculator = $calculator;
    }


    protected function configure()
    {
        $this->setName('sales:init-target-ship')
            ->setDescription("Initialize the target ship dates for orders that don't have one")
            ->addArgument('since', InputArgument::OPTIONAL, 'Order since', '-3 months');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $since = $input->getArgument('since');
        /** @var SalesOrder[] $orders */
        $orders = $this->repo->createBuilder()
            ->byDateOrdered(DateRange::create()->withStart($since))
            ->doesNotHaveTargetShipDate()
            ->getResult();

        $io->writeln(sprintf("Found %s orders since %s.",
            number_format(count($orders)), $since));

        foreach ($orders as $order) {
            $io->write("$order : ");
            $this->calculator->setTargetShipDate($order);
            $date = $order->getTargetShipDate();
            $io->writeln($date === null ? 'NONE' : $date->format('Y-m-d'));
        }

        $this->om->flush();
    }
}
