<?php

namespace Rialto\Stock\Transfer;

use DateTime;
use Rialto\Stock\Bin\StockBin;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * When a location transfer item is not received at the destination,
 * this class is used to locate that items and adjust allocations and
 * stock levels accordingly.
 */
class MissingTransferItem
{
    const LOCATION_ORIGIN = 'source';
    const LOCATION_DESTINATION = 'destination';
    const LOCATION_MISSING = 'missing';

    /** @var TransferItem */
    private $item;

    /** @var Transfer */
    private $transfer;

    /**
     * @var integer
     * @Assert\Type(type="numeric", message="Quantity found must be numeric.")
     * @Assert\Range(min=0, minMessage="Quantity found cannot be negative.")
     */
    private $qtyFound;

    /** @var DateTime */
    private $dateFound;

    /** @var string */
    private $location = null;

    public static function getLocationChoices(self $item)
    {
        $src = $item->getOrigin();
        $dest = $item->getDestination();
        return [
            $dest->getName() => self::LOCATION_DESTINATION,
            $src->getName() => self::LOCATION_ORIGIN,
            self::LOCATION_MISSING => self::LOCATION_MISSING,
        ];
    }

    public function __construct(TransferItem $item)
    {
        assertion($item->isMissing());
        $this->item = $item;
        $this->transfer = $this->item->getTransfer();
        $this->qtyFound = $item->getQtySent();
        $this->dateFound = new DateTime();
    }

    /** @return TransferItem */
    public function getTransferItem()
    {
        return $this->item;
    }

    /** @return Transfer */
    public function getTransfer()
    {
        return $this->transfer;
    }

    public function getOrigin()
    {
        return $this->transfer->getOrigin();
    }

    public function getDestination()
    {
        return $this->transfer->getDestination();
    }

    public function getStockItem()
    {
        return $this->item->getStockItem();
    }

    public function getDescription()
    {
        return $this->item->getDescription();
    }

    public function getQtySent()
    {
        return $this->item->getQtySent();
    }

    public function getQtyReceived()
    {
        return $this->item->getQtyReceived();
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function getQtyFound()
    {
        return $this->qtyFound;
    }

    public function setQtyFound($qtyFound)
    {
        $this->qtyFound = $qtyFound;
    }

    /**
     * @return DateTime
     */
    public function getDateFound()
    {
        return clone $this->dateFound;
    }

    /**
     * @param DateTime $dateFound
     */
    public function setDateFound(DateTime $dateFound)
    {
        $this->dateFound = clone $dateFound;
    }

    /** @return StockBin */
    public function getStockBin()
    {
        return $this->item->getStockBin();
    }
}
