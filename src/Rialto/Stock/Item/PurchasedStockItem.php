<?php

namespace Rialto\Stock\Item;

use Rialto\Database\Orm\DbManager;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Symfony\Component\Validator\Constraints as Assert;

class PurchasedStockItem extends PhysicalStockItem
{
    const STOCK_TYPE = 'purchased';

    /**
     * @var float|null
     */
    private $minTemperature;

    /**
     * @var float|null
     */
    private $maxTemperature;

    public function getStockType(): string
    {
        return self::STOCK_TYPE;
    }

    /**
     * @Assert\Valid
     */
    public function getTemperatureRange()
    {
        return new TemperatureRange($this->minTemperature, $this->maxTemperature);
    }

    public function setTemperatureRange(TemperatureRange $range = null)
    {
        $this->minTemperature = $range ? $range->getMin() : null;
        $this->maxTemperature = $range ? $range->getMax() : null;
    }

    public function getProducerRepository(DbManager $dbm)
    {
        return $dbm->getRepository(PurchaseOrderItem::class);
    }

    public function isEsdSensitive(): bool
    {
        return $this->getCategory()->isEsdSensitive();
    }
}
