<?php

namespace Rialto\Stock\ChangeNotice;

use Rialto\Database\Orm\Persistable;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Version;

/**
 * Links stock items and versions to change notices.
 */
class ChangeNoticeItem implements Persistable
{
    private $id;

    /** @var ChangeNotice */
    private $changeNotice;

    /**
     * The item to which this notice applies.
     * @var StockItem
     */
    private $stockItem;

    /**
     * The version to which this notice applies.
     *
     * @var string
     */
    private $version;

    public function __construct(ChangeNotice $notice, StockItem $item)
    {
        $this->changeNotice = $notice;
        $this->stockItem = $item;
        $this->version = Version::ANY;
    }

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getStockCode()
    {
        return $this->stockItem->getSku();
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        return new Version($this->version);
    }

    public function setVersion(Version $version)
    {
        $this->version = (string) $version;
    }

    public function matches(Item $item)
    {
        $codesMatch = $this->getStockCode() == $item->getSku();
        if ( $item instanceof ItemVersion ) {
            return $codesMatch && $item->getVersionCode() == $this->version;
        }
        return $codesMatch;
    }

    public function getFullSku()
    {
        return $this->getStockCode() . $this->getVersion()->getStockCodeSuffix();
    }

    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getEntities()
    {
        return [$this];
    }

}
