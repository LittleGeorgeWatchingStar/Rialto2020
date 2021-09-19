<?php

namespace Rialto\Stock\Item;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Bom\Bom;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Stock\Item\Version\Version;

class ManufacturedStockItem extends PhysicalStockItem implements CompositeStockItem
{
    const STOCK_TYPE = 'manufactured';

    public function getStockType(): string
    {
        return self::STOCK_TYPE;
    }

    /**
     * Returns a bill of materials (BOM) for this item.
     *
     * @param Version $version
     *  The BOM for this version will be returned.
     */
    public function getBom(Version $version = null): Bom
    {
        if (null === $version) {
            $version = $this->getAutoBuildVersion();
        } elseif (!$version->isSpecified()) {
            $version = $this->getAutoBuildVersion();
        } else {
            $version = $this->getVersion($version);
        }
        return $version->getBom();
    }

    /**
     * True if the bill of materials (BOM) for the given version has been
     * created.
     */
    public function bomExists(Version $version = null): bool
    {
        $bom = $this->getBom($version);
        return count($bom) > 0;
    }

    public function getProducerRepository(DbManager $dbm)
    {
        return $dbm->getRepository(WorkOrder::class);
    }

    public function getTemperatureRange()
    {
        return $this->getBom()->getTemperatureRange();
    }

    public function isEsdSensitive(): bool
    {
        return $this->isBoard() ? true : $this->getBom()->isEsdSensitive();
    }
}
