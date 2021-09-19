<?php

namespace Rialto\Manufacturing\Task\Cli;


use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Manufacturing\Task\Orm\ProductionTaskRepository;
use Rialto\Manufacturing\Task\ProductionTask;
use Rialto\Manufacturing\Task\ProductionTaskFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshProductionTasksCommand extends ContainerAwareCommand
{
    /** @var DoctrineDbManager */
    private $dbm;

    /** @var ProductionTaskRepository */
    private $repo;

    /** @var ProductionTaskFactory */
    private $factory;

    protected function configure()
    {
        $this->setName('production:refresh-tasks')
            ->setAliases(['rialto:production:refresh-tasks'])
            ->setDescription("Regenerate work order tasks")
            ->addOption('force', null, InputOption::VALUE_NONE,
                'Force refresh of all open orders');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dbm = $this->getContainer()->get(DbManager::class);
        $this->repo = $this->dbm->getRepository(ProductionTask::class);
        $this->factory = $this->getContainer()->get(ProductionTaskFactory::class);

        $orders = $input->getOption('force')
            ? $this->repo->findAllOrders()
            : $this->repo->findOrdersToUpdate();
        foreach ($orders as $po) {
            $this->dbm->beginTransaction();
            try {
                $tasks = $this->factory->refreshTasks($po);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $count = number_format(count($tasks));
            $supplier = $po->getSupplierName();
            $output->writeln("Regenerated $count tasks for $po ($supplier)");
        }
    }

}
