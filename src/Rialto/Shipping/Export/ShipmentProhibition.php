<?php

namespace Rialto\Shipping\Export;

use Gumstix\GeographyBundle\Model\Country;
use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Describes a stock item, stock category, or ECCN code that is prohibited
 * from being shipped to a certain country.
 */
class ShipmentProhibition implements RialtoEntity
{
    private $id;

    /** @Assert\NotBlank */
    private $prohibitedCountry;
    private $stockItem = null;
    private $stockCategory = null;
    private $eccnCode = '';

    /** @Assert\Length(max="1000") */
    private $notes = '';

    public function getId()
    {
        return $this->id;
    }

    /** @return Country */
    public function getProhibitedCountry()
    {
        return $this->prohibitedCountry ? new Country($this->prohibitedCountry) : null;
    }

    public function setProhibitedCountry(Country $prohibitedCountry)
    {
        $this->prohibitedCountry = $prohibitedCountry->getCode();
    }

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function setStockItem(StockItem $item = null)
    {
        $this->stockItem = $item;
    }

    public function getStockCategory()
    {
        return $this->stockCategory;
    }

    public function setStockCategory(StockCategory $category = null)
    {
        $this->stockCategory = $category;
    }

    public function getEccnCode()
    {
        return $this->eccnCode;
    }

    public function setEccnCode($code)
    {
        $this->eccnCode = trim($code);
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($notes)
    {
        $this->notes = trim($notes);
    }
}
