<?php

namespace Rialto\Sales\Discount;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Item\StockItem;

class DiscountGroup implements RialtoEntity
{
    private $id;
    private $name;

    /** @var StockItem[] */
    private $items;

    /** @var DiscountRate[] */
    private $rates;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->rates = new ArrayCollection();
    }

    public function getItems()
    {
        return $this->items->toArray();
    }

    public function addItem(StockItem $item)
    {
        $this->items[] = $item;
    }

    public function removeItem(StockItem $item)
    {
        $this->items->removeElement($item);
    }

    public function getRates()
    {
        return $this->rates->toArray();
    }

    public function addRate(DiscountRate $rate)
    {
        $this->rates[] = $rate;
        $rate->setDiscountGroup($this);
    }

    public function removeRate(DiscountRate $rate)
    {
        $this->rates->removeElement($rate);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
