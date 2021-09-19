<?php

namespace Rialto\Magento2\Order\Cli;

use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Rialto\Database\Orm\DbManager;
use Rialto\Logging\Cli\LoggingCommand;
use Rialto\Magento2\Magento2Events;
use Rialto\Magento2\Order\Order;
use Rialto\Magento2\Order\OrderSynchronizerInterface;
use Rialto\Magento2\Order\SuspectedFraudEvent;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\SalesEvents;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Pull the latest sales orders from Magento storefronts.
 *
 * Magento does not appear to have any webhooks or callbacks to notify
 * Rialto of new orders, so we have to periodically fetch them. This has
 * advantages: if Rialto goes down, it just picks up where it left off when
 * it comes back up.
 */
class SyncOrdersCommand extends LoggingCommand
{
    const NAME = 'magento2:sync-orders';

    /** @var DbManager */
    private $dbm;

    /** @var OrderSynchronizerInterface */
    private $sync;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var SymfonyStyle */
    private $io;

    /** @var InputInterface */
    private $input;

    public function __construct(DbManager $dbm,
                                OrderSynchronizerInterface $sync,
                                EventDispatcherInterface $dispatcher,
                                LoggerInterface $logger)
    {
        parent::__construct(self::NAME, $logger);
        $this->dbm = $dbm;
        $this->sync = $sync;
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this->setDescription('Pull the latest sales orders from Magento2 storefronts')
            ->addOption('since', null, InputOption::VALUE_OPTIONAL,
                'Import orders since this date')
            ->addOption('overlap', null, InputOption::VALUE_OPTIONAL,
                'Adjust the starting date by this much', '-5 minutes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->input = $input;

        $storefronts = $this->loadStorefronts();
        if (count($storefronts) == 0) {
            $this->io->error("No storefronts defined.");
            return;
        }
        foreach ($storefronts as $store) {
            $this->io->section("Syncing orders for $store");
            try {
                $this->syncOrders($store);
            } catch (ClientException $ex) {
                $msg = (string) $ex->getResponse();
                $this->io->writeln("<error>ERROR: $msg</error>");
            }
        }
    }

    /** @return Storefront[] */
    private function loadStorefronts()
    {
        return $this->dbm->getRepository(Storefront::class)
            ->findAll();
    }

    private function syncOrders(Storefront $store)
    {
        $since = $this->getSyncFromDate($store);
        $this->debug("Sync from: " . $since->format('Y-m-d H:i:s'));
        $newOrders = $this->sync->getOrderList($store, $since);
        foreach ($newOrders as $order) {
            $incrementId = $order->getCustomerReference();
            $this->io->write("Order $incrementId ");
            if ($this->sync->alreadyExists($order, $store)) {
                $this->io->writeln('already exists: SKIPPED.');
            } else {
                $this->io->write('is new: ');
                $this->tryToCreateOrder($order);
            }
        }
    }

    /**
     * Sync orders starting from this date.
     * @return \DateTime|null If null, sync all orders.
     */
    private function getSyncFromDate(Storefront $store)
    {
        $lastUpdate = $this->findLastUpdate($store);
        $this->debug("Last updated: " . ($lastUpdate ? $lastUpdate->format('Y-m-d H:i:s') : 'never'));
        if (null === $lastUpdate) {
            return new \DateTime('30 days ago');
        }

        $since = clone $lastUpdate;
        $since->modify($this->input->getOption('overlap'));
        return $since;
    }

    private function debug($msg)
    {
        if ($this->io->isVerbose()) {
            $this->io->writeln($msg);
        }
    }

    /** @return \DateTime|null */
    private function findLastUpdate(Storefront $store)
    {
        $since = $this->input->getOption('since');
        if ($since) {
            return new \DateTime($since);
        }
        return $this->sync->findDateOfMostRecentOrder($store);
    }

    private function tryToCreateOrder(Order $order)
    {
        $this->dbm->beginTransaction();
        try {
            if ($order->isSuspectedFraud()) {
                $this->notifyOfSuspectedFraud($order);
                $this->dbm->rollBack();
                $this->io->writeln("<error>SUSPECTED FRAUD</error>");
                return;
            }
            if ($order->isCanceled()) {
                $this->dbm->rollBack();
                $this->io->writeln("<error>CANCELED ORDER</error>");
                return;
            }
            if ($order->isMissingCardAuthoriation()) {
                $this->dbm->rollBack();
                $this->io->writeln("<error>MISSING CARD INFO</error>");
                $this->warning("Ambiguous Credit Card: Order " . $order->getCustomerReference());
                return;
            }
            $rialtoOrder = $this->sync->createOrder($order);
            $this->dbm->flush();
            $this->notifyOfNewOrder($rialtoOrder);
            $this->dbm->flushAndCommit();
            $this->io->writeln("CREATED.");
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }

    private function notifyOfSuspectedFraud(Order $order)
    {
        $event = new SuspectedFraudEvent($order);
        $this->dispatcher->dispatch(Magento2Events::SUSPECTED_FRAUD, $event);
    }

    private function notifyOfNewOrder(SalesOrder $order)
    {
        $event = new SalesOrderEvent($order);
        $this->dispatcher->dispatch(SalesEvents::ORDER_AUTHORIZED, $event);
    }
}
