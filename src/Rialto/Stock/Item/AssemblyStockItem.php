<?php

namespace Rialto\Stock\Item;

use Rialto\Manufacturing\Bom\Bom;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Validator\Constraints as Assert;

class AssemblyStockItem extends StockItem implements CompositeStockItem
{
    const STOCK_TYPE = 'assembly';

    public function getStockType(): string
    {
        return self::STOCK_TYPE;
    }

    /**
     * Returns a bill of materials (BOM) for this item.
     *
     * @param Version $version The BOM for this version will be returned.
     */
    public function getBom(Version $version = null): Bom
    {
        if ( null === $version ) {
            $version = $this->getAutoBuildVersion();
        } elseif (! $version->isSpecified() ) {
            $version = $this->getAutoBuildVersion();
        }
        $version = $this->getVersion($version);
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

    /**
     * Returns the weight of this product, in kilograms.
     *
     * @return double
     */
    public function getWeight()
    {
        $bom = $this->getBom();
        return $bom->getTotalWeight();
    }

    public function getVolume()
    {
        $volume = 0;
        $bom = $this->getBom();
        foreach ( $bom as $bomItem ) { /* @var $bomItem BomItem */
            $component = $bomItem->getComponent();
            $quantity = $bomItem->getQuantity();
            $volume += $quantity * $component->getVolume();
        }
        return $volume;
    }

    /**
     * @Assert\Range(min=0.0001,
     *   minMessage="Standard cost must be at least {{ limit }}.",
     *   groups={"standardCost","purchasing"})
     * @return double
     */
    public function getStandardCost()
    {
        $bom = $this->getBom();
        return $bom->getTotalStandardCost();
    }

    public function isEsdSensitive(): bool
    {
        return $this->getBom()->isEsdSensitive();
    }
}
