<?php

namespace Rialto\Stock\Item;

use Rialto\Database\Orm\DbManager;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Purchasing\Producer\Orm\StockProducerRepository;

/**
 * Most stock items are physical items; exceptions are assemblies, which
 * are really a collection of stock items, and dummy items, which are non-
 * physical things like services.
 */
abstract class PhysicalStockItem extends StockItem
{
    /**
     * @return double
     *  the weight of this product, in kilograms.
     */
    public function getWeight()
    {
        $version = $this->getShippingVersion();
        return $version->getWeight();
    }

    /**
     * @return double
     *  The physical volume of this stock item, in cubic centimeters.
     */
    public function getVolume()
    {
        $version = $this->getShippingVersion();
        return $version->getVolume();
    }

    /**
     * Returns the repository that can be used to find open stock
     * producers of this item.
     *
     * @return StockProducerRepository
     */
    public abstract function getProducerRepository(DbManager $dbm);

    /**
     * @return TemperatureRange
     */
    public abstract function getTemperatureRange();
}
