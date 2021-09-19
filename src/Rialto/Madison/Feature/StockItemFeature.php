<?php

namespace Rialto\Madison\Feature;

use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Item\StockItem;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Indicates a Madison feature provided by a stock item.
 *
 * @UniqueEntity(fields={"stockItem", "featureCode"},
 *   message="This item already provides that feature.")
 */
class StockItemFeature implements RialtoEntity
{
    /**
     * @var StockItem
     */
    private $stockItem;

    /**
     * @var string
     */
    private $featureCode;

    /**
     * @var string
     * @Assert\Length(max=255)
     */
    private $value = '';

    /**
     * @var string
     * @Assert\Length(max=255, maxMessage="Details should be no more than {{ limit }} characters.")
     */
    private $details = '';

    public function __construct(StockItem $stockItem, $featureCode)
    {
        $this->stockItem = $stockItem;
        $this->featureCode = $featureCode;
    }

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getFeatureCode()
    {
        return $this->featureCode;
    }

    public function setValue($value)
    {
        $this->value = trim($value);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = trim($details);
    }
}

