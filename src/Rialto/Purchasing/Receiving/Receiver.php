<?php

namespace Rialto\Purchasing\Receiving;

use Psr\Log\LoggerInterface;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\PurchasingEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Receives a GRN for a purchase order and implements the GRN transaction.
 */
class Receiver
{
    /** @var ItemReceiver */
    private $itemReceiver;

    /** @var DbManager */
    private $dbm;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ItemReceiver $itemReceiver,
                                DbManager $dbm,
                                EventDispatcherInterface $dispatcher,
                                LoggerInterface $logger)
    {
        $this->itemReceiver = $itemReceiver;
        $this->dbm = $dbm;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * Records stock and accounting transaction for the given GRN.
     * @return Transaction
     */
    public function receive(GoodsReceivedNotice $grn)
    {
        assertion(null != $grn->getId());
        $this->logger->notice(sprintf('Receiving %s...',
            $grn->getDescription()
        ));

        $transaction = Transaction::fromEvent($grn);
        $items = $grn->getItems();
        assertion(count($items) > 0);
        foreach ($items as $grnItem) {
            $this->itemReceiver->receiveItem($grnItem, $transaction);
        }
        $this->dbm->persist($transaction);

        $this->notify($grn);

        return $transaction;
    }

    public function reverseReceipt(GoodsReceivedItem $original, $qtyToReverse)
    {
        return $this->itemReceiver->reverseReceipt($original, $qtyToReverse);
    }

    private function notify(GoodsReceivedNotice $grn)
    {
        $event = new GoodsReceivedEvent($grn);
        $this->dispatcher->dispatch(PurchasingEvents::GOODS_RECEIVED, $event);
    }
}
