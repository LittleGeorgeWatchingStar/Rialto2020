<?php

namespace Rialto\Stock;

use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Customization\Customizable;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;


/**
 * An item has has a specific version and customization, such as:
 * @see SalesOrderDetail
 * @see BomItem
 */
interface VersionedItem extends Item, Customizable
{
    /** @return Version */
    public function getVersion();

    /** @return StockItem */
    public function getStockItem();

    /**
     * @deprecated use getFullSku() instead.
     */
    public function getVersionedStockCode();

    /**
     * @return string The full SKU, including revision and customization
     *   codes; eg "GS3503F-R1234-C10085"
     */
    public function getFullSku();
}
