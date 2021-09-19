<?php

namespace Rialto\Stock\Transfer;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Accounting\Transaction\TransactionInitiator;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Shipping\Method\ShipperDefaultShippingMethod;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Order\RatableOrder;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Location;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A transfer of stock from one location to another.
 */
class Transfer implements RialtoEntity, TransactionInitiator, Location, RatableOrder
{

    private $id;

    /**
     * The location from which stock will be transferred.
     * @var Facility
     * @Assert\NotNull
     */
    private $origin;

    /**
     * The location to which stock will be transferred.
     * @var Facility
     * @Assert\NotNull
     */
    private $destination;

    /**
     * When it was requested.
     *
     * @var DateTime
     */
    private $dateRequested;

    /**
     * When it was picked and packed.
     *
     * @var DateTime|null
     */
    private $dateKitted;

    /**
     * When it was picked up by the delivery person.
     *
     * @var DateTime|null
     */
    private $dateShipped;

    /**
     * When it was reported (or inferred to be) received at the destination.
     * @var DateTime|null
     */
    private $dateReceived;

    /** @var Shipper */
    private $shipper;

    /** @var string|null */
    private $shippingMethod = null;

    /** @var TransferItem[] */
    private $lineItems;

    /** @var PurchaseOrder[] */
    private $purchaseOrders;

    /**
     * Transient field to record the name of the person who picked up the
     * transfer, if known.
     *
     * @var string|null
     */
    private $pickedUpBy = null;

    /** @var string[] */
    private $trackingNumbers = [];

    public function __construct(Facility $from)
    {
        $this->origin = $from;
        $this->lineItems = new ArrayCollection();
        $this->purchaseOrders = new ArrayCollection();
        $this->dateRequested = new DateTime();
    }

    /**
     * Creates a new Transfer object for a transfer
     * of stock between the given locations.
     *
     * @return Transfer
     */
    public static function fromLocations(Facility $from, Facility $to)
    {
        assertion(!$from->equals($to));

        $transfer = new self($from);
        $transfer->destination = $to;
        return $transfer;
    }

    /**
     * @return StockBin[]
     * @Assert\Count(min=1,
     *   minMessage="At least one bin must be selected.",
     *   groups={"create"})
     */
    public function getBins()
    {
        return $this->lineItems->map(function (TransferItem $item) {
            return $item->getStockBin();
        })->getValues();
    }

    /**
     * @param StockBin $bin
     * @return TransferItem
     */
    public function addBin(StockBin $bin)
    {
        $error = $this->validateBin($bin);
        if ($error) {
            throw new \InvalidArgumentException($error);
        }
        $detail = new TransferItem($this, $bin);
        $this->lineItems[] = $detail;
        return $detail;
    }

    /**
     * @param StockBin $bin
     * @return string|null
     *  An error message if the bin is not valid; null if it is.
     */
    public function validateBin(StockBin $bin)
    {
        if (!$bin->isAtSublocationOf($this->getOrigin())) {
            return sprintf('%s is not at the source location (%s)',
                $bin, $this->getOrigin()
            );
        }
        return null;
    }

    /**
     * @return bool True if this transfers contains $bin.
     */
    public function hasBin(StockBin $bin)
    {
        return null !== $this->getBinIndex($bin);
    }

    public function removeBin(StockBin $bin)
    {
        $index = $this->getBinIndex($bin);
        if (null !== $index) {
            $this->lineItems->remove($index);
        }
    }

    private function getBinIndex(StockBin $bin)
    {
        foreach ($this->lineItems as $i => $item) {
            if ($bin->equals($item->getStockBin())) {
                return $i;
            }
        }
        return null;
    }

    /**
     * Make sure that all of the bins being sent are needed at the
     * destination location.
     *
     * @Assert\Callback(groups={"create"})
     */
    public function validateAllocations(ExecutionContextInterface $context)
    {
        foreach ($this->getBins() as $bin) {
            $allocations = $bin->getAllocations();
            foreach ($allocations as $alloc) {
                $this->validateAllocation($alloc, $bin, $context);
            }
        }
    }

    private function validateAllocation(
        StockAllocation $alloc,
        StockBin $bin,
        ExecutionContextInterface $context)
    {
        // TODO: Temporarily Disabled to handle massive stock returns.
        return;
        if ($this->origin->isColocatedWith($this->destination)) {
            return;
        }
        $consumer = $alloc->getConsumer();
        $neededAt = $consumer->getLocation();
        if (!$this->destination->equals($neededAt)) {
            $context
                ->buildViolation(
                    "$bin is needed at $neededAt by _consumer.", [
                    '_consumer' => strtolower($alloc->getConsumerDescription())
                ])
                ->atPath('bins')
                ->addViolation();
        }
    }

    /**
     * @Assert\Callback(groups={"create"})
     */
    public function validateLocationsNotEqual(ExecutionContextInterface $context)
    {
        if ($this->origin->equals($this->destination)) {
            $context->buildViolation(
                "Cannot transfer to the same location.")
                ->atPath('destination')
                ->addViolation();
        }
    }

    /**
     * @return DateTime
     */
    public function getDateRequested()
    {
        return clone $this->dateRequested;
    }

    /**
     * @return DateTime|null
     */
    public function getDateShipped()
    {
        return $this->dateShipped ? clone $this->dateShipped : null;
    }

    /**
     * @return DateTime|null
     */
    public function getDateSent()
    {
        return $this->getDateShipped();
    }

    public function isSent()
    {
        return null !== $this->dateShipped;
    }

    /**
     * @return DateTime
     */
    public function getDateReceived()
    {
        return $this->dateReceived ? clone $this->dateReceived : null;
    }

    public function setReceived(DateTime $date = null)
    {
        assertion($this->isSent(), "$this is not sent");
        $this->dateReceived = $date ? clone $date : new DateTime();
    }

    public function isReceived()
    {
        return null !== $this->dateReceived;
    }

    /**
     * @return TransferItem[]
     *  Any items that were missing when this transfer was received.
     */
    public function getMissingItems()
    {
        if (!$this->isReceived()) {
            return [];
        }
        $missing = [];
        foreach ($this->lineItems as $item) {
            if (!$item->isReceived()) {
                $missing[] = $item;
            }
        }
        return $missing;
    }

    /** @return bool */
    public function hasMissingItems()
    {
        return count($this->getMissingItems()) > 0;
    }

    /**
     * @return TransferItem[]
     *  Any items whose qty received is less than qty sent.
     */
    public function getShortItems()
    {
        if (!$this->isReceived()) {
            return [];
        }
        $short = [];
        foreach ($this->lineItems as $item) {
            if (!$item->isReceived()) {
                continue;
            }
            if ($item->getQtyReceived() < $item->getQtySent()) {
                $short[] = $item;
            }
        }
        return $short;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getGroupNo()
    {
        return $this->getId();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName()
    {
        return sprintf('stock transfer %s', $this->id);
    }

    public function getDescription()
    {
        return sprintf('%s from %s to %s',
            $this->getName(),
            $this->origin,
            $this->destination);
    }

    /**
     * @return bool
     */
    public function equals(Location $other = null)
    {
        return ($other instanceof Transfer)
            ? ($this->id == $other->id)
            : false;
    }

    public function getShipper(): ?Shipper
    {
        return $this->shipper;
    }

    /**
     * @return ShippingMethod|null
     */
    public function getShippingMethod()
    {
        if (!$this->shipper) {
            return null;
        }

        // TODO: Remove this, this is for old transfers with no persisted
        // shipping method.
        if (!$this->shippingMethod) {
            return new ShipperDefaultShippingMethod($this->shipper);
        };

        return $this->shipper->getShippingMethod($this->shippingMethod);
    }

    public function setShippingMethod(ShippingMethod $method = null)
    {
        if ($method) {
            $this->shipper = $method->getShipper();
            $this->shippingMethod = $method->getCode();
        } else {
            $this->shippingMethod = null;
        }
    }

    public function isPickupRequired()
    {
        return $this->shipper
        && $this->shipper->isHandCarried()
        && (!$this->isSent());
    }

    /**
     * @return TransferItem[]
     */
    public function getLineItems()
    {
        return $this->lineItems->toArray();
    }

    public function isEmpty()
    {
        return count($this->lineItems) === 0;
    }

    /**
     * @param StockBin $bin
     * @return TransferItem
     * @throws \InvalidArgumentException if $bin is not in this transfer
     */
    public function getItem(StockBin $bin)
    {
        foreach ($this->lineItems as $item) {
            if ($item->getStockBin()->equals($bin)) {
                return $item;
            }
        }
        throw new \InvalidArgumentException("$this does not include $bin");
    }

    public function getMemo()
    {
        return sprintf('Transfer from %s to %s',
            $this->getOrigin(),
            $this->getDestination()
        );
    }

    /**
     * @return Facility
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    public function setOrigin(Facility $origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return Facility
     */
    public function getDestination()
    {
        return $this->destination;
    }

    public function setDestination(Facility $destination)
    {
        $this->destination = $destination;
    }

    public function getDestinationName()
    {
        return $this->destination->getName();
    }

    public function isDestinedFor(Facility $location)
    {
        return $this->destination->equals($location);
    }

    /**
     * True if this is a kit to a supplier, rather than an internal transfer.
     *
     * @return bool
     */
    public function isForSupplier()
    {
        return $this->destination->hasSupplier();
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        if ($this->isForSupplier()) {
            return $this->destination->getSupplier();
        }
        throw new \LogicException("$this is not destined for a supplier");
    }

    /**
     * @return SupplierContact[]
     */
    public function getSupplierContacts()
    {
        return $this->isForSupplier()
            ? $this->getSupplier()->getKitContacts()
            : [];
    }

    /**
     * @return string
     */
    public function getSystemTypeId()
    {
        return SystemType::LOCATION_TRANSFER;
    }

    public function hasOrders()
    {
        return count($this->purchaseOrders) > 0;
    }

    /** @return PurchaseOrder[] */
    public function getPurchaseOrders()
    {
        return $this->purchaseOrders->toArray();
    }

    public function addPurchaseOrder(PurchaseOrder $po)
    {
        $this->errorIfSent();
        $this->purchaseOrders[] = $po;
    }

    public function removePurchaseOrder($po)
    {
        $this->errorIfSent();
        $this->purchaseOrders->removeElement($po);
    }

    public function resetPurchaseOrders(PurchaseOrder $po)
    {
        $this->errorIfSent();
        $this->purchaseOrders->clear();
        $this->addPurchaseOrder($po);
    }

    private function errorIfSent()
    {
        if ($this->isSent()) {
            throw new IllegalStateException("Cannot modify sent $this");
        }
    }

    /**
     * @return DateTime|null
     */
    public function getDateKitted()
    {
        return $this->dateKitted ? clone $this->dateKitted : null;
    }

    public function isKitted()
    {
        return null !== $this->dateKitted;
    }

    /**
     * Indicates that the bins in this transfer have been moved into a box
     * and are ready to be sent.
     */
    public function kit(Transaction $transaction)
    {
        $this->validateKit();
        $this->dateKitted = new DateTime();
        $transaction->setDate($this->dateKitted);
        foreach ($this->lineItems as $detail) {
            $detail->kit($transaction);
        }
    }

    private function validateKit()
    {
        assertion($this->id, "transfer must be persisted before kitting");
        assertion(count($this->lineItems) > 0, "$this is empty");
    }

    /**
     * Sends all bins that have been added to the destination
     * location.
     */
    public function send()
    {
        assertion($this->isKitted(), "$this is not kitted yet");
        $this->dateShipped = new DateTime();
    }

    /**
     * @return string
     */
    public function getPickedUpBy()
    {
        return $this->pickedUpBy ?: '';
    }

    /**
     * @param string $name
     */
    public function setPickedUpBy(string $name)
    {
        $this->pickedUpBy = trim($name);
    }

    /**
     * @return string[]
     */
    public function getTrackingNumbers()
    {
        return $this->trackingNumbers;
    }

    public function setTrackingNumbers(string $trackNumber)
    {
        $this->trackingNumbers[] = $trackNumber;
    }

    public function isAutoReceive()
    {
        return $this->destination->isHeadquarters() ||
        $this->destination->isProductTesting();
    }

    public function getDeliveryCompany()
    {
        return $this->destination->getName();
    }

    public function getDeliveryAddress()
    {
        return $this->destination->getAddress();
    }

    public function getTotalWeight()
    {
        $weight = 0;
        foreach ($this->lineItems as $item) {
            $weight += $item->getTotalWeight();
        }
        return $weight;
    }
}
