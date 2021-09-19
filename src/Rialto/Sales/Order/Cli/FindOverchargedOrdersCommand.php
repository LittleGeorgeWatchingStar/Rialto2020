<?php

namespace Rialto\Sales\Order\Cli;

use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\Money;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FindOverchargedOrdersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('rialto:sales:find-overcharged-orders')
            ->setDescription('Find sales orders that may have been overcharged')
            ->addOption('since', null, InputOption::VALUE_REQUIRED, null, '-6 months');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $since = new \DateTime($input->getOption('since'));

        /** @var $qb QueryBuilder */
        $qb = $this->getContainer()
            ->get('doctrine')
            ->getRepository(SalesOrder::class)
            ->createQueryBuilder('so');

        $qb->andWhere('so.dateOrdered >= :since')
            ->setParameter('since', $since)
            ->join('so.cardTransactions', 'ct')
            ->andWhere('ct.amountCaptured > 0');

        $output->writeln(sprintf("%30s   %10s, %10s, %10s",
            "Orders since " . $since->format('Y-m-d'),
            'charged',
            'order tot',
            'inv tot'
        ));

        /** @var $orders SalesOrder[] */
        $orders = $qb->getQuery()->getResult();
        foreach ($orders as $order) {
            $cardTotal = Money::round($this->getTotalCaptured($order));
            $orderTotal = Money::round($order->getTotalPrice());
            $invTotal = Money::round($order->getTotalAmountInvoiced());
            if (($cardTotal > $orderTotal) || ($cardTotal > $invTotal)) {
                $output->writeln(sprintf('%30s : <error>%10.2f, %10.2f, %10.2f</error>',
                    $order,
                    $cardTotal,
                    $orderTotal,
                    $invTotal));
            } else if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(sprintf("%30s : OK", $order));
            }
        }
    }

    private function getTotalCaptured(SalesOrder $order)
    {
        $total = 0;
        foreach ($order->getCardTransactions() as $ct) {
            $total += $ct->getAmountCaptured();
        }
        return $total;
    }

}
