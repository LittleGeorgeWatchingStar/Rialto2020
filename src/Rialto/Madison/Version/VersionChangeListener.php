<?php

namespace Rialto\Madison\Version;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Rialto\Stock\Item\StockItem;

/**
 * Listens for stock items whose shipping versions have changed so that
 * Madison can be notified.
 *
 * @see VersionChangeNotifier which notifies Madison at the end of the request.
 */
class VersionChangeListener
{
    /**
     * @var VersionChangeCache
     */
    private $cache;

    public function __construct(VersionChangeCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * When a stock item's shipping version is updated, hang on to the item
     * for @see notifyMadison() below.
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if ($this->shouldRecordChange($args)) {
            /** @var StockItem $entity */
            $entity = $args->getEntity();
            $this->cache->addItem($entity);
        }
    }

    /**
     * We should only notify Madison if the changed item is a board
     * or product. Otherwise we'll be sending lots of needless requests
     * for items that Madison doesn't know about.
     */
    private function shouldRecordChange(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        return ($entity instanceof StockItem)
            && $entity->isSellable()
            && $args->hasChangedField('shippingVersion');
    }
}
