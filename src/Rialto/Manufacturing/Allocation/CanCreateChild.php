<?php


namespace Rialto\Manufacturing\Allocation;

use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

interface CanCreateChild
{
    /**
     * @return StockItem
     */
    public function getChildItem();

    /**
     * @return Version
     */
    public function getChildVersion();

    /**
     * @return Facility
     */
    public function getBuildLocation();

    /**
     * @return bool
     */
    public function isCreateChild();
}
