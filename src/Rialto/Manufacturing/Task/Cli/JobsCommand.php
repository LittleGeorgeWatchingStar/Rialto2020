<?php

namespace Rialto\Manufacturing\Task\Cli;


use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Task\JobFactory;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JobsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('rialto:production:jobs')
            ->setDescription('Create scheduled jobs for a purchase order')
            ->addArgument('purchaseOrder', InputArgument::REQUIRED,
                'The purchase order ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $dbm DbManager */
        $dbm = $this->getContainer()->get(DbManager::class);

        /** @var $po PurchaseOrder */
        $po = $dbm->need(PurchaseOrder::class, $input->getArgument('purchaseOrder'));
        $factory = new JobFactory($dbm);
        $factory->forPurchaseOrder($po);
        $output->writeln("Added jobs for $po.");
        $dbm->flush();
    }
}
