<?php

namespace Rialto\Stock\Cost;

use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class StandardCost implements Persistable, RialtoEntity, AccountingEvent
{
    const PRECISION = 4;

    /** @var int */
    private $id;

    /**
     * @var StockItem
     * @Assert\NotNull
     */
    private $stockItem;

    /**
     * @var float
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    private $materialCost;

    /**
     * @var float
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    private $labourCost = 0;

    /**
     * @var float
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    private $overheadCost = 0;

    /** @var float|null */
    private $previousCost = null;

    /**
     * @var \DateTime
     * @Assert\DateTime
     */
    private $startDate;

    /**
     * @var string
     */
    private $memo = '';

    /**
     * A factory function that copies the costs of $previous.
     * @return StandardCost
     */
    public static function duplicate(StandardCost $previous)
    {
        $new = new self($previous->getStockItem());
        $new->materialCost = $previous->getMaterialCost();
        $new->labourCost = $previous->getLabourCost();
        $new->overheadCost = $previous->getOverheadCost();
        return $new;
    }

    public function __construct(StockItem $stockItem)
    {
        $this->stockItem = $stockItem;
        $this->startDate = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getStockCode()
    {
        return $this->stockItem->getSku();
    }

    public function getMaterialCost()
    {
        return $this->materialCost;
    }

    public function setMaterialCost($materialCost)
    {
        $this->materialCost = (float) $materialCost;
    }

    public function getLabourCost()
    {
        return $this->labourCost;
    }

    public function setLabourCost($labourCost)
    {
        $this->labourCost = (float) $labourCost;
    }

    public function getOverheadCost()
    {
        return $this->overheadCost;
    }

    public function setOverheadCost($overheadCost)
    {
        $this->overheadCost = (float) $overheadCost;
    }

    /**
     * @return float
     * @Assert\Range(min=0.0001)
     */
    public function getTotalCost()
    {
        return $this->round($this->materialCost + $this->labourCost + $this->overheadCost);
    }

    private function round($value)
    {
        return round($value, self::PRECISION);
    }

    public function getPreviousCost()
    {
        return $this->previousCost;
    }

    public function setPrevious(StandardCost $previous = null)
    {
        $this->previousCost = $previous ? $previous->getTotalCost() : null;
    }

    public function getStartDate()
    {
        return clone $this->startDate;
    }

    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = clone $startDate;
    }

    public function getEntities()
    {
        return [$this];
    }

    public function getDate()
    {
        return $this->getStartDate();
    }

    public function setQtyInStock($qtyInStock)
    {
        $this->memo = sprintf(
            '%s cost was %s changed to %s x quantity on hand of %s',
            $this->getStockCode(),
            ($this->getPreviousCost() === null) ? 'null' : $this->getPreviousCost(),
            $this->getTotalCost(),
            number_format($qtyInStock));
    }

    public function getMemo(): string
    {
        return $this->memo;
    }

    public function getPeriod()
    {
        return Period::fetchForDate($this->getDate());
    }

    public function getSystemType(): SystemType
    {
        return SystemType::fetchCostUpdate();
    }

    public function getSystemTypeNumber()
    {
        return $this->id;
    }
}
