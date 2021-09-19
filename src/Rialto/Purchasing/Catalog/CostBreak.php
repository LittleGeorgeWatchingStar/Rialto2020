<?php

namespace Rialto\Purchasing\Catalog;

/**
 * The cost of a PurchasingData instance at various order quantities and
 * lead times.
 */
class CostBreak extends CostBreakAbstract
{
    private $id;

    /** @var PurchasingData */
    private $purchasingData;

    public function getId()
    {
        return $this->id;
    }

    public function getPurchasingData()
    {
        return $this->purchasingData;
    }

    public function setPurchasingData(PurchasingData $purchData)
    {
        $this->purchasingData = $purchData;
    }

    public function __clone()
    {
        if (! $this->id ) {
            return; // Required by Doctrine
        }

        $this->id = null;
        $this->purchasingData = null;
    }
}
