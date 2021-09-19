<?php

namespace Rialto\Sales\Price;

use Rialto\Accounting\Currency\Currency;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A single price point that we charge for a product.
 *
 * A product can have multiple ProductPrice records for different
 * sales types, currencies, etc.
 */
class ProductPrice implements RialtoEntity
{
    private $id;
    private $stockItem;
    private $salesType;
    private $currency;

    /**
     * @var float
     * @Assert\Type(type="float")
     * @Assert\Range(min=0, minMessage="Price cannot be negative.")
     */
    private $price = 0.0;

    public function __construct(
        StockItem $item,
        Currency $currency,
        SalesType $salesType)
    {
        $this->stockItem = $item;
        $this->currency = $currency;
        $this->salesType = $salesType;
    }

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getSku(): string
    {
        return $this->stockItem->getSku();
    }

    /**
     * @deprecated
     */
    public function getStockCode()
    {
        return $this->getSku();
    }

    public function getSalesType()
    {
        return $this->salesType;
    }

    public function setSalesType(SalesType $salesType = null)
    {
        $this->salesType = $salesType ?: $this->salesType;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency = null)
    {
        $this->currency = $currency ?: $this->currency;
    }

    public function getPrice()
    {
        return (float) $this->price;
    }

    public function setPrice($price)
    {
        $this->price = (float) $price;
    }

    public function getId()
    {
        return $this->id;
    }
}
