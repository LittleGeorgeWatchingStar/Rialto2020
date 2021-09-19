<?php

namespace Rialto\Stock\Shelf\Position;


use Rialto\Stock\Bin\HasStockBins;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Shelf\Velocity;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for when bins are created or change facility and assigns
 * those bins a shelf position when possible.
 */
class AssignmentListener implements EventSubscriberInterface
{
    /**
     * Shelf assignment needs to happen before, eg, printing labels.
     *
     * @see BinLabelListener
     */
    const PRIORITY = 100;

    /**
     * @var PositionAssigner
     */
    private $assigner;

    public function __construct(PositionAssigner $assigner)
    {
        $this->assigner = $assigner;
    }

    public static function getSubscribedEvents()
    {
        static $action = ['onBinMove', self::PRIORITY];
        return [
            StockEvents::BIN_SPLIT => $action,
            StockEvents::MISSING_ITEM_RESOLVED => $action,
            StockEvents::STOCK_CREATION => $action,
            StockEvents::TRANSFER_RECEIPT => $action,
            StockEvents::STOCK_ADJUSTMENT => $action,
        ];
    }

    public function onBinMove(HasStockBins $event)
    {
        /* Because the bin just moved, it is now high-velocity. */
        $velocity = Velocity::high();
        foreach ($event->getBins() as $bin) {
            if ($this->needsShelfPosition($bin)) {
                $this->assigner->assignPosition($bin, $velocity);
            }
        }
    }

    private function needsShelfPosition(StockBin $bin)
    {
        if ($bin->isInTransit()) {
            return false;
        }
        if ($bin->isEmpty()) {
            return false;
        }
        return !$bin->hasShelfPosition();
    }
}
