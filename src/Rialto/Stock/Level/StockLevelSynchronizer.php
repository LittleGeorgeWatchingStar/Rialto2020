<?php

namespace Rialto\Stock\Level;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\AssemblyStockItem;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Synchronizes stock levels with external applications.
 *
 * Dispatches an event which each application bundle must listen for and
 * respond to.
 */
class StockLevelSynchronizer
{
    /** @var ObjectManager */
    private $om;

    /** @var StockLevelStatusRepository */
    private $repo;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(ObjectManager $om, EventDispatcherInterface $dispatcher)
    {
        $this->om = $om;
        $this->repo = $this->om->getRepository(StockLevelStatus::class);
        $this->dispatcher = $dispatcher;
    }

    /** @return StockLevelUpdate[] */
    public function loadUpdates(Facility $facility, int $limit)
    {
        return $this->repo->findStale($facility, StockCategory::PRODUCT, $limit);
    }

    /**
     * Ensure that a StockLevelStatus record exists for $item at $location.
     */
    public function ensureExists(PhysicalStockItem $item, Facility $location)
    {
        $this->repo->findOrCreate($item, $location);
    }

    /** @return StockLevelUpdate */
    public function loadUpdate(PhysicalStockItem $item, Facility $location)
    {
        return $this->repo->findUpdate($item, $location);
    }

    /**
     * Apply the given stock level update and notify external systems.
     *
     * @return string[] Warnings added by listeners, if any.
     */
    public function synchronize(StockLevelUpdate $update): array
    {
        $update->applyUpdate();
        $event = $this->notify($update->getStatus());
        return $event->getWarnings();
    }

    /**
     * Notify external systems (eg storefronts) of the current stock level.
     */
    public function notify(AvailableStockLevel $level): StockLevelEvent
    {
        $event = new StockLevelEvent($level);
        $this->dispatcher->dispatch(StockEvents::STOCK_LEVEL_UPDATE, $event);
        return $event;
    }

    /**
     * Syncs stock levels of assembly items that contain any of the given
     * components.
     *
     * @param Item[] $components
     * @param Facility|string $location
     * @return string[] Warnings added by listeners, if any.
     */
    public function syncAssemblies(array $components, $location)
    {
        $skus = array_unique(array_map(function (Item $item) {
            return $item->getSku();
        }, $components));
        /** @var $repo StockItemRepository */
        $repo = $this->om->getRepository(StockItem::class);
        $assemblies = $repo->findAssembliesContaining($skus);
        $warnings = [];
        foreach ($assemblies as $assembly) {
            $new = $this->syncAssembly($assembly, $location);
            $warnings = array_merge($warnings, $new);
        }
        return $warnings;
    }

    private function syncAssembly(AssemblyStockItem $assembly, $location)
    {
        $level = $this->repo->getAssemblyStockLevel($assembly, $location);
        $event = $this->notify($level);
        return $event->getWarnings();
    }
}
