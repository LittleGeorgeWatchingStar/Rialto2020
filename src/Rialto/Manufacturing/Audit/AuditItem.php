<?php

namespace Rialto\Manufacturing\Audit;

use Rialto\Allocation\Requirement\ConsolidatedRequirement;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Source\StockSource;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Used for checking that all of the stock for a work order requirement
 * is where we think it should be.
 *
 * @see PurchaseOrderAudit
 */
class AuditItem extends ConsolidatedRequirement
{
    /**
     * @var RequirementStatus The allocation status before any adjustments
     * have been made.
     */
    private $initialStatus;

    /**
     * @Assert\Type(type="integer",
     *   message="Quantity adjustment must be a whole number.")
     */
    private $adjustment = 0;

    /** @var AuditFailureAnalysis|null */
    private $failureAnalysis = null;

    public function __construct(Facility $cm)
    {
        $this->initialStatus = new RequirementStatus($cm);
        $this->setShareBins(true);
    }

    public function addRequirement(Requirement $requirement)
    {
        parent::addRequirement($requirement);
        $this->initialStatus->addRequirement($requirement);
    }

    /** @return Facility */
    public function getBuildLocation()
    {
        return $this->getFacility();
    }

    /** @return Supplier */
    public function getSupplier()
    {
        return $this->getFacility()->getSupplier();
    }

    /** @return PurchaseOrder */
    public function getPurchaseOrder()
    {
        foreach ($this->getRequirements() as $req) {
            return $req->getConsumer()->getPurchaseOrder();
        }
        throw new \LogicException("Audit item has no PO");
    }

    public function getAdjustment()
    {
        return $this->adjustment;
    }

    public function hasAdjustment()
    {
        return 0 != $this->adjustment;
    }

    public function setAdjustment($adjustment)
    {
        $this->adjustment = (int) $adjustment;
    }

    public function setActualQty($actual)
    {
        $atCM = $this->initialStatus->getNetQtyAtLocation();
        $this->adjustment = $actual - $atCM;
    }

    public function getActualQty()
    {
        $atCM = $this->initialStatus->getNetQtyAtLocation();
        return $atCM + $this->adjustment;
    }

    /**
     * @Assert\Callback
     */
    public function validateAdjustment(ExecutionContextInterface $context)
    {
        $undelivered = $this->getTotalQtyUndelivered();
        $atCM = $this->initialStatus->getNetQtyAtLocation();
        $newQty = $atCM + $this->adjustment;
        if ($newQty > $undelivered) {
            $context->buildViolation(
                "Total quantity of _item cannot be greater than _needed.", [
                '_item' => $this->getSku(),
                '_needed' => number_format($undelivered)
            ])->atPath('actualQty')->addViolation();
        } elseif ($newQty < 0) {
            $context->buildViolation(
                "Total quantity of _item cannot be less than zero.", [
                '_item' => $this->getSku()
            ])->atPath('actualQty')->addViolation();
        }
    }

    public function getDescription()
    {
        return $this->getStockItem()->getName() . $this->getLabelText();
    }

    public function __toString()
    {
        return $this->getDescription();
    }

    /**
     * @return string The text printed on the label, if this item is a printed
     * label.
     */
    private function getLabelText()
    {
        if ($this->getStockItem()->isPrintedLabel()) {
            // There should be only one.
            foreach ($this->getRequirements() as $requirement) {
                $consumer = $requirement->getConsumer();
                return sprintf(' "%s"', $consumer->getFullSku());
            }
        }
        return '';
    }

    public function getQtyAtLocation()
    {
        return $this->getAllocationStatus()->getNetQtyAtLocation();
    }

    public function getQtyShort()
    {
        return $this->getTotalQtyUndelivered()
            - $this->getQtyAtLocation();
    }

    /**
     * Releases as many allocations as possible from the CM in order to
     * comply with a negative adjustment amount.
     *
     * @return StockSource[] Sources from which allocations were released.
     */
    public function releaseAllocationsFromCM()
    {
        $toRelease = -$this->getAdjustment();
        $sources = [];
        foreach ($this->getAllocations() as $alloc) {
            if ($toRelease <= 0) {
                break;
            }
            if (! $alloc->isAtLocation($this->getFacility())) {
                continue;
            }
            $toRelease += $alloc->adjustQuantity(-$toRelease);
            $sources[] = $alloc->getSource();
        }
        return $sources;
    }

    /**
     * Returns true if the requested adjustments were made successfully.
     */
    public function isSuccessful()
    {
        $newStatus = $this->getAllocationStatus();
        return $newStatus->getNetQtyAtLocation() == $this->getActualQty();
    }

    /**
     * @return RequirementStatus The CURRENT allocation status
     */
    public function getAllocationStatus()
    {
        $status = new RequirementStatus($this->getFacility());
        foreach ($this->getRequirements() as $req) {
            $status->addRequirement($req);
        }
        return $status;
    }

    public function setFailureAnalysis(AuditFailureAnalysis $analysis)
    {
        $this->failureAnalysis = $analysis;
    }

    public function getFailureReason()
    {
        return $this->failureAnalysis
            ? $this->failureAnalysis->getSummary() : '';
    }

    public function getFailureDescription()
    {
        $reason = $this->getFailureReason();
        if (!$reason) {
            return '';
        }
        return sprintf('Unable to adjust %s by %d: %s.',
            $this->getFullSku(),
            $this->getAdjustment(),
            $reason);
    }
}
