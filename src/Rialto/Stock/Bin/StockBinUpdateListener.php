<?php

namespace Rialto\Stock\Bin;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Logging\FlashLogger;
use Rialto\Port\CommandBus\CommandBus;
use Rialto\Stock\Item\Command\RefreshStockLevelCommand;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Responds to stock level changes in Rialto by updating the stock levels
 * in Magento.
 */
class StockBinUpdateListener implements EventSubscriberInterface
{
    /** @var StockItemRepository */
    private $stockItemRepo;

    /** @var CommandBus */
    private $commandBus;

    /** @var FlashLogger */
    private $flashLogger;

    public function __construct(ObjectManager $om, CommandBus $commandBus, FlashLogger $flashLogger)
    {
        $this->stockItemRepo = $om->getRepository(StockItem::class);
        $this->commandBus = $commandBus;
        $this->flashLogger = $flashLogger;

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
        return [
            StockEvents::STOCK_BIN_CHANGE => 'stockBinChangeUpdateStockLevel',
        ];
    }

    public function stockBinChangeUpdateStockLevel(StockBinEvent $event): void
    {
        $sku = $event->getItemSku();
        $command = new RefreshStockLevelCommand($sku);
        $this->commandBus->handle($command);

        $msg = "Refresh Stock Level for $sku successfully.";
        $this->logNotice($msg);
    }

    /** @param string $msg */
    private function logNotice(string $msg): void
    {
        $this->flashLogger->notice($msg);
    }
}
