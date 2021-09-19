<?php

namespace Rialto\Stock\Item;

use Rialto\Entity\EntityAttribute;
use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Allows us to store arbitrary values associated with a stock item.
 *
 * @UniqueEntity(fields={"stockItem", "attribute"},
 *     message="You cannot have the same attribute twice.")
 */
class StockItemAttribute extends EntityAttribute
{
    /**
     * Boolean attribute indicating that the item is a bag suitable for
     * enclosing products.
     */
    const PRODUCT_BAG = 'product bag';

    /**
     * Boolean attribute indicating that the item is a box suitable for
     * enclosing products.
     */
    const PRODUCT_BOX = 'product box';

    /**
     * Numeric attribute indicating the size of the LCD screen.
     */
    const LCD_SIZE = 'lcd display size';

    /**
     * Boolean attribute indicating that the item is a bag suitable for
     * BRDs which need shield against static electricity.
     */
    const SHIELDED_BAG = 'shielded bag';

    /** @var StockItem */
    private $stockItem;

    /** @return string[] */
    public static function getChoices()
    {
        $attr = self::getValidAttributes();
        return array_combine($attr, $attr);
    }

    /** @return string[] */
    private static function getValidAttributes()
    {
        return [
            self::LCD_SIZE,
            self::PRODUCT_BAG,
            self::PRODUCT_BOX,
            self::SHIELDED_BAG,
        ];
    }

    public function setStockItem(StockItem $item)
    {
        $this->stockItem = $item;
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function setEntity(RialtoEntity $entity)
    {
        $this->setStockItem($entity);
    }
}
