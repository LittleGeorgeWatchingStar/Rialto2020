<?php

namespace Rialto\Manufacturing\WorkOrder;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Status\AllocationStatus;
use Rialto\Allocation\Status\AllocationStatusString;
use Rialto\Allocation\Status\ConsumerStatus;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\Bom\Bom;
use Rialto\Manufacturing\Bom\BomException;
use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Customizer;
use Rialto\Manufacturing\Customization\Validator\CustomizationMatchesVersion;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssue;
use Rialto\Manufacturing\WorkOrder\Validator\ParentIsCompatible;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\ItemIndex;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * A work order is a request to have a manufacturer build a board or product.
 *
 * @CustomizationMatchesVersion
 * @ParentIsCompatible
 */
class WorkOrder extends StockProducer implements StockConsumer
{
    const DIRTY_ALLOCATIONS = 1;
    const DIRTY_REQUIREMENTS = 2;

    /**
     * This flag indicates that the CM can start building this order.
     */
    const FLAG_APPROVED_TO_BUILD = 'ok_to_build';

    /**
     * @param WorkOrder[] $workOrders
     * @return WorkOrder[] indexed by id.
     */
    public static function indexById($workOrders)
    {
        $index = [];
        foreach ($workOrders as $wo) {
            $index[$wo->getId()] = $wo;
        }
        return $index;
    }

    private $qtyIssued = 0;

    /**
     * @var Customization|null
     */
    private $customization = null;

    /** @var WorkOrder|null */
    private $parent;

    /** @var WorkOrder|null */
    private $child;

    /**
     * Custom instructions for this work order.
     * @var string
     */
    private $instructions = '';

    /** @var bool */
    private $rework = false;

    /**
     * @Assert\Valid(traverse=true)
     * @var Requirement[]
     */
    private $requirements;

    /** @var WorkOrderIssue[] */
    private $issues;

    /**
     * Indicates whether the dependent records of this work order need
     * to be updated.
     * @var int
     */
    private $dirty = 0;

    public function __construct(PurchaseOrder $po,
                                PurchasingData $purchData,
                                Version $version = null)
    {
        parent::__construct($po);
        $this->initializePurchasingData($purchData);
        $this->version = (string) $purchData->getSpecifiedVersion($version);

        $stockItem = $purchData->getStockItem();
        $this->description = "Labour: $stockItem";
        assertion($purchData->getBuildLocation() !== null);

        $this->requirements = new ArrayCollection();
        $this->issues = new ArrayCollection();
        $this->addFlag(self::FLAG_APPROVED_TO_BUILD);
    }

    public function __toString()
    {
        return sprintf('%s order %s',
            $this->rework ? 'rework' : 'work',
            $this->getId());
    }

    protected function validateStockItem(StockItem $item)
    {
        assertion($item instanceof ManufacturedStockItem);
    }

    public function bomExists()
    {
        return $this->getStockItem()->bomExists($this->getVersion());
    }

    /**
     * Returns the UNCUSTOMIZED bill of materials (BOM) for this work order.
     *
     * @throws BomException If the BOM does not exist yet.
     */
    public function getBom(): Bom
    {
        /* @var $bom Bom */
        $bom = $this->getStockItem()->getBom($this->getVersion());

        if ($bom->isEmpty()) {
            throw new BomException($bom, "$bom is empty");
        }
        return $bom;
    }

    /** @Assert\Callback */
    public function validateBom(ExecutionContextInterface $context)
    {
        if (!$this->bomExists()) {
            $context->addViolation(sprintf('BOM does not exist for %s',
                $this->getFullSku()));
        }
    }

    /**
     * @return bool
     */
    public function canBeIssued()
    {
        /* A work order can theoretically be issued multiple times, each
         * time for a fraction of the total amount ordered. */
        return $this->hasRequirements() &&
            (!$this->isClosed()) &&
            ($this->getQtyIssued() < $this->getQtyOrdered());
    }

    public function canBeSent()
    {
        return parent::canBeSent() && $this->hasRequirements();
    }

    public function isSent()
    {
        return $this->purchaseOrder->isSent();
    }

    public function setSent($sender, $note)
    {
        $this->purchaseOrder->setSent($sender, $note);
    }

    /**
     * @return bool
     */
    public function canBeReceived()
    {
        if ($this->isClosed()) return false;
        if ($this->isIssued()) return true;
        return false;
    }

    /**
     * A turnkey build is one in which the supplier will provide most of the
     * components needed to build the item.
     *
     * @return bool
     *  True if this is a turnkey build.
     */
    public function isTurnkey()
    {
        return $this->purchasingData->isTurnkey();
    }

    /**
     * Returns the parent work order of this work order.
     *
     * @see hasParent()
     * @return WorkOrder|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(WorkOrder $parent = null)
    {
        if ($parent) {
            assertion(!$this->hasParent());
            assertion(!$parent->hasChild());
            $this->parent = $parent;
            $parent->child = $this;
        } else {
            $this->parent->child = null;
            $this->parent = null;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function hasChild()
    {
        return (bool) $this->getChild();
    }

    /**
     * @return WorkOrder|null
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Returns this work order all others that are linked to it
     * via parent-child relationships.
     *
     * @return WorkOrderFamily
     */
    public function getFamily()
    {
        return new WorkOrderFamily($this);
    }

    /**
     * True if there is a purchase order associated with this work order.
     *
     * @deprecated All work orders have POs now.
     * @return boolean
     */
    public function hasPurchaseOrder()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getPurchaseOrder() !== null;
    }

    public function hasSupplier()
    {
        return $this->purchaseOrder->hasSupplier();
    }

    public function getPurchaseOrderNumber()
    {
        return $this->getOrderNumber();
    }

    public function isForSameOrder(StockConsumer $other)
    {
        return ($other instanceof WorkOrder) &&
            ($this->getOrderNumber() == $other->getOrderNumber());
    }

    public function getAllocationStatus(): AllocationStatus
    {
        return new ConsumerStatus($this);
    }

    public function getAllocationStatusString(): string
    {
        return new AllocationStatusString($this->getAllocationStatus());
    }

    /**
     * True if all of the required components are allocated and present at
     * the manufacturer.
     */
    public function isKitComplete(): bool
    {
        $status = $this->getAllocationStatus();
        return $status->isKitComplete();
    }

    /**
     * Returns the work order requirements for this work order, if any.
     *
     * @return Requirement[]
     */
    public function getRequirements()
    {
        return $this->requirements->toArray();
    }

    /**
     * @return RequirementStatus[]
     */
    public function getRequirementStatuses()
    {
        return array_map(function (Requirement $requirement) {
            $status = New RequirementStatus($requirement->getFacility());
            $status->addRequirement($requirement);
            return $status;
        }, $this->getRequirements());
    }

    public function createRequirement(PhysicalStockItem $component,
                                      $unitQty,
                                      WorkType $workType): Requirement
    {
        $requirement = new Requirement($this, $component, $unitQty, $workType);
        $this->addRequirement($requirement);
        return $requirement;
    }

    private function addRequirement(Requirement $requirement)
    {
        $this->requirements[] = $requirement;

        if ($requirement->isVersioned()) {
            $this->setDirty(self::DIRTY_ALLOCATIONS);
        }
    }

    public function removeRequirement(Requirement $woReq)
    {
        $this->requirements->removeElement($woReq);
        if ($woReq->isVersioned()) {
            $this->setDirty(self::DIRTY_ALLOCATIONS);
        }
    }

    public function hasRequirements(): bool
    {
        return count($this->requirements) > 0;
    }

    /**
     * True if this work order has a requirement for $item.
     */
    public function hasRequirement(Item $item): bool
    {
        return null != $this->getRequirementOrNull($item);
    }

    /**
     * Helper method to hasRequirement() and getRequirement().
     * @return Requirement|null
     */
    private function getRequirementOrNull(Item $item)
    {
        foreach ($this->requirements as $woReq) {
            if ($woReq->getSku() == $item->getSku()) {
                return $woReq;
            }
        }
        return null;
    }

    /**
     * @throws \InvalidArgumentException if this work order does not have
     *  a requirement for $item.
     */
    public function getRequirement(Item $item): Requirement
    {
        $req = $this->getRequirementOrNull($item);
        if ($req) {
            return $req;
        }
        $sku = $item->getSku();
        throw new \InvalidArgumentException("$this has no requirement for $sku");
    }

    /** @return WorkType[] */
    public function getWorkTypesNeeded(): array
    {
        $types = [];
        foreach ($this->requirements as $req) {
            $workType = $req->getWorkType();
            $types[$workType->getId()] = $workType;
        }
        return $types;
    }

    /**
     * @return WorkOrder[] Orders at $location upon which this work order
     *  depends.
     */
    public function getPrepWorkAtLocation(Facility $location): array
    {
        $all = [];
        foreach ($this->requirements as $req) {
            $all = array_merge($all, $req->getPrepWorkAtLocation($location));
        }
        return $all;
    }

    public function getCustomizedBom(Customizer $customizer): Bom
    {
        $bom = $this->getBom()->createCopy();
        if ($this->customization) {
            $customizer->customize($bom, $this->customization);
        }
        return $bom;
    }

    /**
     * Modify the requirements of this work order to match the
     * components in $bom.
     */
    public function resetRequirements(ItemIndex $bom)
    {
        assertion(!$this->isIssued());
        foreach ($bom as $component) {
            /** @var $component Component */
            $this->updateRequirement($component);
        }
        foreach ($this->requirements as $req) {
            if (!$bom->contains($req)) {
                $this->removeRequirement($req);
            }
        }
    }

    /**
     * Ensures that $component is among the requirements and updates its
     * quantity, designators, etc.
     */
    private function updateRequirement(Component $component)
    {
        $req = $this->getRequirementOrNull($component);
        if ($component->getUnitQty() == 0) {
            if ($req) {
                $this->removeRequirement($req);
            }
            return;
        }

        if (!$req) {
            $req = $this->createRequirement(
                $component->getStockItem(),
                $component->getUnitQty(),
                $component->getWorkType());
        }
        $req->setUnitQtyNeeded($component->getUnitQty());
        $req->setWorkType($component->getWorkType());
        $req->setDesignators($component->getDesignators());
        $req->setVersionOrDefault($component->getVersion());
        $req->setCustomization($component->getCustomization());
    }

    /**
     * Make sure the version and customization of the child requirement match
     * those of the child work order.
     */
    public function setChildRequirementVersionAndCustomization()
    {
        if (!$this->hasChild()) {
            return;
        }
        $child = $this->getChild();
        $requirement = $this->getRequirement($child);
        $requirement->setVersion($child->getVersion());
        $requirement->setCustomization($child->getCustomization());
    }

    /**
     * Returns all components in the work order, including turnkey items
     * which are provided by the manufacturer.
     *
     * @return Component[]
     */
    public function getAllComponents(): array
    {
        if ($this->isRework()) {
            return $this->getRequirements();
        } elseif ($this->isTurnkey()) {
            return $this->mergeTurnkeyAndConsignmentComponents();
        } else {
            return $this->getRequirements();
        }
    }

    /** @return Component[] */
    private function mergeTurnkeyAndConsignmentComponents(): array
    {
        $index = new ItemIndex();
        $consignment = $this->getRequirements();
        foreach ($consignment as $woReq) {
            $index->add($woReq);
        }

        $all = $this->getBom();
        foreach ($all as $bomItem) {
            if ($index->contains($bomItem)) continue;
            $index->add($bomItem);
        }
        return array_values($index->toArray());
    }

    /**
     * True of any of this order's requirements have allocations.
     */
    public function hasRequestedAllocations(): bool
    {
        foreach ($this->requirements as $woReq) {
            if (count($woReq->getAllocations()) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Customization|null
     */
    public function getCustomization()
    {
        return $this->customization;
    }

    public function hasCustomizations(): bool
    {
        return null !== $this->customization;
    }

    public function setCustomization(Customization $cust = null)
    {
        if (Customization::areEqual($this->customization, $cust)) {
            return;
        }

        $this->customization = $cust;
        $this->setDirty(self::DIRTY_REQUIREMENTS);
    }

    public function getIdSummary(): string
    {
        $id = $this->getId();
        if ($this->hasChild()) {
            $id = "$id & " . $this->getChild()->getId();
        }
        return "$id, " . $this->getLocation()->getName();
    }

    /**
     * Custom instructions for this work order.
     */
    public function getInstructions(): string
    {
        return (string) $this->instructions;
    }

    public function setInstructions($instructions)
    {
        $this->instructions = trim($instructions);
    }

    /**
     * Appends $text to the custom instructions.
     * @param string $text
     */
    public function appendInstructions($text)
    {
        $text = trim($text);
        if ($text) {
            $this->instructions .= PHP_EOL . $text;
        }
    }

    /**
     * @return Facility
     */
    public function getLocation()
    {
        return $this->purchaseOrder->getBuildLocation();
    }

    /**
     * True if $location is where this order is being built.
     */
    public function isLocation(Facility $location): bool
    {
        return $location->equals($this->getLocation());
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent->getId();
    }

    /** @deprecated */
    public function getStandardCost()
    {
        return $this->getStandardUnitCost();
    }

    /**
     * @return float
     *  The standard cost of each unit of the item being built.
     */
    public function getStandardCostPerUnit()
    {
        $cost = 0;
        foreach ($this->getRequirements() as $woReq) {
            $cost += $woReq->getExtendedStandardCost();
        }
        return $cost;
    }

    public function isApprovedBySupplier(): bool
    {
        return $this->purchaseOrder->isApproved();
    }

    public function isRejectedBySupplier(): bool
    {
        return $this->purchaseOrder->isRejected();
    }

    public function isPendingApproval(): bool
    {
        return $this->purchaseOrder->isPendingApproval();
    }

    public function getFullSku()
    {
        return $this->getSku()
            . $this->getVersion()->getStockCodeSuffix()
            . Customization::getStockCodeSuffix($this->customization);
    }

    /**
     * @deprecated
     */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /**
     * @return int|float
     */
    public function getQtyIssued()
    {
        return $this->qtyIssued;
    }

    /**
     * Increments the number of units issued by $qtyIssued.
     *
     * @param int|float $qtyIssued
     */
    public function addQtyIssued($qtyIssued)
    {
        $this->qtyIssued += $qtyIssued;
    }

    /**
     * True if this has been issued, false if not.
     */
    public function isIssued(): bool
    {
        return $this->qtyIssued > 0;
    }

    public function isFullyIssued(): bool
    {
        return $this->qtyIssued >= $this->qtyOrdered;
    }

    /**
     * True if this has ever been issued, even if those issues have been
     * reversed.
     */
    public function hasEverBeenIssued(): bool
    {
        return count($this->issues) > 0;
    }

    /** @return WorkOrderIssue[] */
    public function getIssues(): array
    {
        return $this->issues->toArray();
    }

    public function addIssue(WorkOrderIssue $issue)
    {
        $this->issues[] = $issue;
    }

    /**
     * @return int|float
     * @Assert\Range(min=0, minMessage="Qty ordered cannot be less than qty issued.")
     */
    public function getQtyUnissued()
    {
        return $this->getQtyOrdered() - $this->getQtyIssued();
    }

    public function getQtyIssuedButNotReceived()
    {
        return $this->getQtyIssued() - $this->getQtyReceived();
    }

    /**
     * @return float
     */
    public function getTotalValueIssued()
    {
        $total = 0;
        foreach ($this->issues as $issue) {
            $total += $issue->getTotalValueIssued();
        }
        return $total;
    }

    public function getQtyInProcess()
    {
        return $this->getQtyIssued() - $this->getQtyReceived();
    }

    public function hasWorkInProcess(): bool
    {
        return $this->getQtyInProcess() > 0;
    }

    public function isInProcess(): bool
    {
        return parent::isInProcess() || $this->isIssued();
    }

    /**
     * The total number of units that were built but discarded because
     * of manufacturing defects.
     *
     * @return int
     */
    public function getQtyFailed()
    {
        $total = 0;
        foreach ($this->purchaseOrder->getReceipts() as $grn) {
            if ($grn->hasItem($this)) {
                $grnItem = $grn->getItem($this);
                if ($grnItem->isDiscarded()) {
                    $total += $grnItem->getQtyReceived();
                }
            }
        }
        return $total;
    }

    /**
     * The version of the stock item to build.
     * @return Version
     */
    public function getVersion()
    {
        return new Version($this->version);
    }

    /**
     * @param Version $version The version of the stock item to build.
     */
    public function setVersion(Version $version)
    {
        if (!$version->isSpecified()) {
            throw new \InvalidArgumentException("version for $this must be specified");
        }
        if ($version->equals($this->version)) {
            return;
        }
        $this->version = (string) $version;
        $this->setDirty(self::DIRTY_REQUIREMENTS);
        if ($this->getParent()) {
            $this->getParent()->setDirty(self::DIRTY_REQUIREMENTS);
        }
    }

    /**
     * True if this work order has a parent work order.
     */
    public function hasParent(): bool
    {
        return null !== $this->parent;
    }

    /**
     * Cancels any unissued quantity for this work order and closes it.
     * Recursively cancels the parent work order, if there is one.
     */
    public function cancel()
    {
        $this->cancelRequirementAllocations();
        parent::cancel();

        if ($this->hasParent()) {
            $this->parent->cancel();
        }
    }

    /**
     * Cancels any allocations for which this order's requirements are
     * the consumers.
     */
    private function cancelRequirementAllocations()
    {
        foreach ($this->requirements as $woReq) {
            $woReq->closeAllocations();
        }
    }

    public function setCommitmentDate(DateTime $date = null)
    {
        parent::setCommitmentDate($date);
        if ($this->hasChild()) {
            $this->child->setCommitmentDate($date);
        }
    }

    protected function getDefaultCommitmentDate()
    {
        return $this->isTurnkey()
            ? new \DateTime('+2 weeks')
            : parent::getDefaultCommitmentDate();
    }

    /**
     * If the user changes something critical, such as the version or
     * customization, then certain dependent records, such as requirements,
     * will become outdated.
     */
    public function setDirty(int $code)
    {
        if ($this->isIssued() && (self::DIRTY_REQUIREMENTS == $code)) {
            throw new IllegalStateException("Cannot modify an issued work order");
        }

        $this->setUpdated();

        /* Rework orders have completely custom requirements and instructions,
         * so this does not apply to them. */
        if ($this->rework) return;

        /* Only setClean() can lower the dirtiness level. */
        $this->dirty = max($code, $this->dirty);
    }

    public function isDirty(int $code = self::DIRTY_ALLOCATIONS)
    {
        return $this->dirty >= $code;
    }

    /**
     * DependencyUpdater calls this method once it has done
     * the work necessary to restore the work order to a consistent state.
     */
    public function setClean()
    {
        $this->dirty = 0;
        $this->setUpdated();
    }

    /**
     * Sets the total quantity ordered to $newQty.
     *
     * @param int $newQty
     */
    public function setQtyOrdered($newQty)
    {
        if ($newQty != $this->qtyOrdered) {
            parent::setQtyOrdered($newQty);
            $this->setDirty(self::DIRTY_ALLOCATIONS);
        }
        return $this;
    }

    /** @Assert\Callback */
    public function validateQtyOrdered(ExecutionContextInterface $context)
    {
        if ($this->qtyOrdered < $this->getQtyIssued()) {
            $context->buildViolation(
                'Quantity ordered cannot be less than quantity issued (_iss)', [
                '_iss' => number_format($this->getQtyIssued()),
            ])
                ->atPath('qtyOrdered')
                ->addViolation();
        }

        if ($this->hasParent()) {
            $parentQty = $this->parent->getQtyOrdered();
            if ($this->qtyOrdered < $parentQty) {
                $context->buildViolation(
                    "Child quantity ordered (_c) cannot be less than that of parent (_p).",
                    [
                        '_c' => number_format($this->qtyOrdered),
                        '_p' => number_format($parentQty)
                    ])
                    ->atPath('qtyOrdered')
                    ->addViolation();
            }
        }
        if ($this->hasChild()) {
            $child = $this->getChild();
            $child->validateQtyOrdered($context);
        }
    }

    public function isRework(): bool
    {
        return (bool) $this->rework;
    }

    public function setRework($bool)
    {
        $this->rework = (bool) $bool;
    }

    public function getFlagOptions(): array
    {
        $flags = parent::getFlagOptions();
        $flags['OK to build'] = self::FLAG_APPROVED_TO_BUILD;
        return $flags;
    }

    public function isApprovedToBuild(): bool
    {
        return $this->hasFlag(self::FLAG_APPROVED_TO_BUILD);
    }
}
