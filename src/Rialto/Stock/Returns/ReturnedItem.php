<?php

namespace Rialto\Stock\Returns;

use DateTime;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Location;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A stock bin returned to us from a manufacturer where there is some question
 * or ambiguity about which bin it is or whether it was consumed as expected
 * by the manufacturer.
 *
 * The user can fill in any of the fields of this class, which are used to
 * deduce which bin was returned and what state it is now in.
 *
 * @UniqueEntity(
 *   fields={"bin"},
 *   ignoreNull=true,
 *   message="One of the bins you entered has already been checked in.",
 *   groups={"Default", "flow_ReturnedItems_step1"})
 */
class ReturnedItem implements RialtoEntity, Item
{
    /** @var int */
    private $id;

    /** @var DateTime */
    private $dateCreated;

    /** @var Facility */
    private $returnedFrom;

    /** @var Facility */
    private $returnedTo;

    /** @var StockBin */
    private $bin = null;

    /** @var StockItem */
    private $item = null;

    /**
     * @var BinStyle
     * @Assert\NotNull(message="Please choose a bin style.",
     *     groups={"flow_ReturnedItems_step2"})
     */
    private $binStyle = null;

    /**
     * The manufacturer's part number.
     * @var string
     */
    private $manufacturerCode = '';

    /**
     * The supplier's catalog number.
     * @var string
     */
    private $catalogNumber = '';

    /**
     * The work order PO to which this part was allocated
     * @var PurchaseOrder
     */
    private $buildPO = null;

    /**
     * The parts PO from which this part originated
     * @var PurchaseOrder
     */
    private $partsPO = null;

    /** @var string */
    private $supplierReference = '';

    /**
     * @var int
     * @Assert\NotBlank(groups={"flow_ReturnedItems_step2"})
     */
    private $quantity = null;

    public function __construct()
    {
        $this->dateCreated = new DateTime();
    }

    /**
     * Factory method. This is used when initially receiving an item whose
     * bin ID is known.
     *
     * @return self
     */
    public static function fromBin(StockBin $bin)
    {
        $item = new self();
        $item->bin = $bin;
        $item->binStyle = $bin->getBinStyle();
        $item->item = $bin->getStockItem();
        $item->quantity = $bin->getQtyRemaining();
        $item->manufacturerCode = $bin->getManufacturerCode();
        return $item;
    }

    public function setLocations(Facility $returnedFrom, Facility $returnedTo)
    {
        $this->returnedFrom = $returnedFrom;
        $this->returnedTo = $returnedTo;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return sprintf('unresolved item #%s from %s',
            $this->id,
            $this->returnedFrom);
    }

    /**
     * @return Facility
     */
    public function getReturnedFrom()
    {
        return $this->returnedFrom;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return clone $this->dateCreated;
    }

    /**
     * @return string[]
     */
    public function getProblems()
    {
        if ($this->bin) {
            return [];
        } else {
            return [
                'unidentified bin',
            ];
        }
    }


    public function hasProblems()
    {
        return count($this->getProblems()) > 0;
    }

    /**
     * @return StockBin
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * This is called when resolving a previously unidentified item.
     */
    public function setBin(StockBin $bin = null)
    {
        $this->bin = $bin;
        $this->updateBinStyle();
    }

    public function getBinStyle()
    {
        return $this->binStyle;
    }

    public function setBinStyle(BinStyle $style = null)
    {
        $this->binStyle = $style;
        $this->updateBinStyle();
    }

    /**
     * We often send parts in, eg, a reel, and those same parts get
     * returned in a pouch, so we need to update the bin record accordingly.
     */
    private function updateBinStyle()
    {
        if ($this->bin && $this->binStyle) {
            $this->bin->setBinStyle($this->binStyle);
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateBin(ExecutionContextInterface $context)
    {
        if (!$this->bin) {
            return;
        }
        if ($this->item && (!$this->bin->containsItem($this->item))) {
            $context->buildViolation('_bin does not contain _item.')
                ->setParameter('_bin', $this->bin)
                ->setParameter('_item', $this->item)
                ->atPath('bin')
                ->addViolation();
        }
    }

    /** @return bool */
    public function hasBin()
    {
        return null != $this->bin;
    }

    public function isBin(StockBin $bin)
    {
        return $bin->equals($this->bin);
    }

    public function equals(ReturnedItem $other = null)
    {
        return $other
            && $this->hasBin()
            && $this->isBin($other->getBin());
    }

    public function getBinId()
    {
        return $this->bin ? $this->bin->getId() : null;
    }

    public function hasItem()
    {
        return null !== $this->item;
    }

    /**
     * @return StockItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param StockItem $item
     */
    public function setItem(StockItem $item = null)
    {
        $this->item = $item;
    }

    public function getSku()
    {
        return $this->item ? $this->item->getSku() : null;
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /**
     * @return string
     */
    public function getManufacturerCode()
    {
        return $this->manufacturerCode;
    }

    /**
     * @param string $manufacturerCode
     */
    public function setManufacturerCode($manufacturerCode)
    {
        $this->manufacturerCode = trim($manufacturerCode);
    }

    /**
     * @return string
     */
    public function getCatalogNumber()
    {
        return $this->catalogNumber;
    }

    /**
     * @param string $catalogNumber
     */
    public function setCatalogNumber($catalogNumber)
    {
        $this->catalogNumber = trim($catalogNumber);
    }

    /**
     * @return PurchaseOrder
     */
    public function getBuildPO()
    {
        return $this->buildPO;
    }

    /**
     * @param PurchaseOrder $buildPO
     */
    public function setBuildPO(PurchaseOrder $buildPO = null)
    {
        $this->buildPO = $buildPO;
    }

    /**
     * @Assert\Callback(groups={"flow_ReturnedItems_step2"})
     */
    public function validateBuildLocation(ExecutionContextInterface $context)
    {
        if (!$this->buildPO) {
            return;
        }
        $po = $this->buildPO;
        $poLocation = $po->getBuildLocation();
        if (!$this->returnedFrom->equals($poLocation)) {
            $loc = $this->returnedFrom;
            $context->buildViolation(ucfirst("$po is from $poLocation, not $loc."))
                ->atPath('buildPO')
                ->addViolation();
        }
    }

    /**
     * @return PurchaseOrder
     */
    public function getPartsPO()
    {
        return $this->partsPO;
    }

    /**
     * @param PurchaseOrder $partsPO
     */
    public function setPartsPO(PurchaseOrder $partsPO = null)
    {
        $this->partsPO = $partsPO;
    }

    /**
     * @Assert\Callback(groups={"flow_ReturnedItems_step2"})
     */
    public function validatePartsPO(ExecutionContextInterface $context)
    {
        if (!($this->partsPO && $this->item)) {
            return;
        }
        if (!$this->partsPO->hasLineItem($this->item)) {
            $po = $this->partsPO;
            $item = $this->item;
            $context->buildViolation(ucfirst("$po does not contain $item."))
                ->atPath('partsPO')
                ->addViolation();
        }
    }

    /**
     * @return string
     */
    public function getSupplierReference()
    {
        return $this->supplierReference;
    }

    /**
     * @param string $supplierReference
     */
    public function setSupplierReference($supplierReference)
    {
        $this->supplierReference = trim($supplierReference);
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getExpectedQty()
    {
        return $this->bin ? $this->bin->getQtyRemaining() : null;
    }

    public function getQtyDifference()
    {
        return $this->quantity - $this->getExpectedQty();
    }

    public function getUnitStandardCost()
    {
        return $this->bin ? $this->bin->getUnitStandardCost() : null;
    }

    /**
     * The monetary value of the difference between the expected and actual
     * quantities.
     *
     * @return float|null
     */
    public function getStandardCostDifference()
    {
        if (!$this->bin) {
            return null;
        }
        return $this->getUnitStandardCost() * $this->getQtyDifference();
    }

    /**
     * @return bool True if the quantity reported by the receiver is
     * different than the last known quantity on the bin.
     */
    public function hasQtyMismatch()
    {
        if (!$this->hasBin()) {
            return false;
        }
        return $this->quantity != $this->getExpectedQty();
    }

    /**
     * @return Transfer|Location|null
     */
    public function getOutstandingTransfer()
    {
        if ($this->hasBin() && $this->bin->isInTransit()) {
            return $this->bin->getLocation();
        }
        return null;
    }

    /** @return StockAllocation[] */
    public function getAllocations()
    {
        return $this->bin ? $this->bin->getAllocations() : [];
    }
}
