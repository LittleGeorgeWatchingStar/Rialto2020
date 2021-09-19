<?php

namespace Rialto\Manufacturing\Task\Cli;

use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Rialto\Logging\Cli\LoggingCommand;
use Rialto\Manufacturing\Task\ProductionTaskFactory;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Regenerate production tasks for a purchase order.
 */
class TasksCommand extends LoggingCommand
{
    const NAME = 'rialto:production:tasks';

    /** @var ObjectManager */
    private $om;

    /** @var ProductionTaskFactory */
    private $factory;

    public function __construct(ObjectManager $om,
                                ProductionTaskFactory $factory,
                                LoggerInterface $logger)
    {
        parent::__construct(self::NAME, $logger);
        $this->om = $om;
        $this->factory = $factory;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Regenerate production tasks for a purchase order')
            ->addArgument('purchaseOrder', InputArgument::REQUIRED,
                'The purchase order ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderNo = $input->getArgument('purchaseOrder');
        /** @var $po PurchaseOrder */
        $po = $this->om->find(PurchaseOrder::class, $orderNo);
        if (!$po) {
            $this->warning("PO $orderNo does not exist; it may have been deleted.");
            return;
        }

        $tasks = $this->factory->refreshTasks($po);
        $this->om->flush();
        $this->notice(sprintf('Generated %s tasks for %s.',
            number_format(count($tasks)),
            $po));
    }

}
