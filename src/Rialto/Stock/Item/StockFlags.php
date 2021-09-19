<?php

namespace Rialto\Stock\Item;

use Doctrine\Common\Collections\Collection;

/**
 * Represents all of the stock flags for a single stock item.
 */
class StockFlags
{
    private $stockItem;
    private $flags;

    public function __construct(StockItem $stockItem, Collection $flags)
    {
        $this->stockItem = $stockItem;
        $this->flags = [];
        foreach ( $flags as $flag ) {
            $this->flags[$flag->getName()] = $flag;
        }
    }

    /**
     * This function defines all of the valid flags for a stock item.
     * @return string[] An array of valid flags.
     */
    public function getValidFlags()
    {
        return [
            'componentOfInterest',
            'matingConnector',
            'standardConnector'];
    }

    public function getStockCode()
    {
        return $this->stockItem->getSku();
    }

    /**
     * Returns the value of the given flag.
     * @param string $flagname The name of the flag whose value is being queried.
     * @return string The value of the flag.
     */
    public function getFlag($flagname)
    {
        return isset($this->flags[$flagname]) ?
            $this->flags[$flagname]->getValue() : null;
    }

    public function setFlag($flagname, $value)
    {
        if (! isset($this->flags[$flagname]) ) {
            $this->flags[$flagname] = new StockFlag($this->stockItem, $flagname);
            $this->stockItem->addFlag($this->flags[$flagname]);
        }
        $this->flags[$flagname]->setValue($value);
    }

    public function isComponentOfInterest()
    {
        return (bool) $this->getFirstFlag();
    }

    public function setComponentOfInterest($value)
    {
        $this->setFlag('componentOfInterest', $value);
    }

    public function isMatingConnector()
    {
        return (bool) $this->getFlag('matingConnector');
    }

    public function setMatingConnector($value)
    {
        $this->setFlag('matingConnector', $value);
    }

    public function isStandardConnector()
    {
        return (bool) $this->getFlag('standardConnector');
    }

    public function setStandardConnector($value)
    {
        $this->setFlag('standardConnector', $value);
    }

    /**
     * @todo This method is a bit of a kludge to deal with
     * https://mantis.gumstix.com/view.php?id=2528
     *
     * A better solution is prefered.
     *
     * @return string|null
     */
    public function getFirstFlag()
    {
        foreach ( $this->getValidFlags() as $flagname ) {
            if ( $this->getFlag($flagname) ) return $flagname;
        }
        return null;
    }
}



