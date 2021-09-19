<?php

namespace Rialto\Stock\Item;

use Rialto\Entity\RialtoEntity;

/**
 * A boolean flag containing additional information about a stock item.
 */
class StockFlag implements RialtoEntity
{
    private $stockItem;
    private $flagName;
    private $flagValue;

    public function __construct(StockItem $item, $flagName)
    {
        $this->stockItem = $item;
        $this->flagName = $flagName;
    }

    public function getId()
    {
        return join(RialtoEntity::ID_DELIM, [
            $this->stockItem->getId(),
            $this->flagName,
        ]);
    }

    public function getName()
    {
        return $this->flagName;
    }

    public function getValue()
    {
        return $this->flagValue;
    }

    public function setValue($value)
    {
        $this->flagValue = $value;
    }

}
