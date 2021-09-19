<?php

namespace Rialto\Manufacturing\Kit;

use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Shipment\ShipmentOption;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A kit is a collection of parts needed for one or more work orders.
 *
 * Once a kit is assembed at the warehouse, it populates and sends the
 * stock transfer.
 *
 * @see Transfer
 */
class WorkOrderKit
{
    /** @var Transfer */
    private $transfer;

    private $kitRequirements = null;

    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
        $this->createRequirements();
    }

    /**
     * @return Transfer
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

    private function createRequirements()
    {
        /* Calculate totals for each work order */
        $this->kitRequirements = [];
        foreach ($this->getWorkOrders() as $wo) {
            /* for each requirement */
            foreach ($wo->getRequirements() as $woReq) {
                if ($woReq->isProvidedByChild()) {
                    continue;
                }
                $kitReq = $this->createRequirement($woReq);
                $kitReq->addRequirement($woReq);
            }
        }

        /* sort index by stock id */
        ksort($this->kitRequirements, SORT_STRING);
    }

    /** @deprecated Use getOrigin() instead */
    public function getOriginLocation()
    {
        return $this->getOrigin();
    }

    /** @return Facility */
    public function getOrigin()
    {
        return $this->transfer->getOrigin();
    }

    /** @return Facility */
    public function getDestination()
    {
        return $this->transfer->getDestination();
    }

    /**
     * The supplier who will receive this kit.
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->getDestination()->getSupplier();
    }

    /** @deprecated */
    public function getDestinationLocation()
    {
        return $this->getDestination();
    }

    /**
     * The work orders whose components are included in this transfer.
     *
     * @return WorkOrder[]
     */
    public function getWorkOrders()
    {
        $orders = [];
        foreach ($this->transfer->getPurchaseOrders() as $po) {
            foreach ($po->getWorkOrders() as $wo) {
                $orders[] = $wo;
            }
        }
        return $orders;
    }

    /**
     * @return KitRequirement[]
     */
    public function getRequirements()
    {
        return $this->kitRequirements;
    }

    /** @return KitRequirement */
    private function createRequirement(Requirement $woReq)
    {
        $key = $woReq->getFullSku();

        if (empty($this->kitRequirements[$key])) {
            $kitReq = KitRequirement::fromKit($this);
            $this->kitRequirements[$key] = $kitReq;
        }
        return $this->kitRequirements[$key];
    }

    /** @return KitRequirement */
    public function getRequirement($fullSku)
    {
        if (empty($this->kitRequirements[$fullSku])) {
            throw new \InvalidArgumentException(
                "No such kit requirement $fullSku");
        }
        return $this->kitRequirements[$fullSku];
    }

    /**
     * Adds the allocated stock sources to the location transfer, so that
     * they will be moved to the destination location.
     */
    public function populateTransfer()
    {
        foreach ($this->getRequirements() as $kitReq) {
            $groups = $kitReq->getAllocationGroupsAtOrigin();
            foreach ($groups as $group) {
                if ($group->getQtyAllocated() <= 0) continue;
                $source = $group->getSource();
                if ($source instanceof StockBin) {
                    $this->transfer->addBin($source);
                } else {
                    throw new \UnexpectedValueException(sprintf(
                        'Unexpected stock source type %s', get_class($source)
                    ));
                }
            }
        }
    }

    /** @Assert\Callback */
    public function validateSomethingToSend(ExecutionContextInterface $context)
    {
        foreach ($this->getRequirements() as $kitReq) {
            if ($kitReq->getQtyAllocatedAtOrigin() > 0) return;
        }

        $context->addViolation("Nothing selected to send.");
    }

    public function getShippingMethod(): ?ShippingMethod
    {
        return $this->transfer->getShippingMethod();
    }

    /**
     * @param ShippingMethod|ShipmentOption|null $method
     */
    public function setShippingMethod($method = null)
    {
        if ($method instanceof ShipmentOption) {
            $method = $method->getShippingMethod();
        }
        $this->transfer->setShippingMethod($method);
    }
}
