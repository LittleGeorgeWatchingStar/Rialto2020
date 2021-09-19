<?php

namespace Rialto\Stock\Bin\Label;

use Rialto\Stock\Bin\HasStockBins;
use Rialto\Stock\Shelf\Position\AssignmentListener;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for changes to stock bins and prints new labels for them.
 */
class BinLabelListener implements EventSubscriberInterface
{
    /**
     * Printing labels needs to happen after, eg, assigning bins to shelves.
     *
     * @see AssignmentListener
     */
    const PRIORITY = -100;

    /** @var BinLabelPrintQueue */
    private $printQueue;

    public function __construct(BinLabelPrintQueue $queue)
    {
        $this->printQueue = $queue;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        static $action = ['printLabels', self::PRIORITY];
        return [
            StockEvents::TRANSFER_SENT => $action,
            StockEvents::STOCK_CREATION => $action,
            StockEvents::STOCK_ADJUSTMENT => $action,
            StockEvents::BIN_SPLIT => $action,
        ];
    }

    public function printLabels(HasStockBins $event)
    {
        $this->printQueue->printLabels($event->getBins());
    }
}
