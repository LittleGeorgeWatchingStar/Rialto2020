<?php

namespace Rialto\Stock\Item;

use Rialto\Stock\Item;
use Rialto\Stock\Publication\UrlPublication;

/**
 * Represents a component of a product that may be of interest to a customer.
 */
class ComponentOfInterest implements Item
{
    /** @var StockItem */
    private $item;

    /** @var string */
    private $type;

    private $qty = 0;

    /** @var UrlPublication */
    private $specs;

    public function __construct(StockItem $item)
    {
        $this->item = $item;
    }

    public function getSku()
    {
        return $this->item->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getName()
    {
        return $this->item->getName();
    }

    public function getDescription()
    {
        return $this->item->getLongDescription();
    }

    public function getQuantity()
    {
        return $this->qty;
    }

    public function addQuantity($qty)
    {
        $this->qty += $qty;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = trim($type);
    }

    public function setSpecs(UrlPublication $pub = null)
    {
        $this->specs = $pub;
    }

    public function getSpecs()
    {
        return $this->specs ? $this->specs->getUrl() : null;
    }

}
