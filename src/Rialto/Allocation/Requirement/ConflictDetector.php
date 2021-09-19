<?php

namespace Rialto\Allocation\Requirement;

use Rialto\Allocation\Allocation\AllocationValidator;
use Rialto\Allocation\Allocation\InvalidAllocationException;
use Rialto\Allocation\Allocation\StockAllocation;
use Zend\Validator\ValidatorInterface;

/**
 * Detects conflicts between a StockAllocation and a set of StockConsumers.
 *
 * A conflict means that the consumers cannot share the stock source with
 * the existing allocation; the consumers will have to allocate from a
 * different source.
 */
class ConflictDetector
{
    /** @var ValidatorInterface */
    private $validator;

    public function __construct()
    {
        $this->setValidator($this->getDefaultValidator());
    }

    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    private function getDefaultValidator()
    {
        return new AllocationValidator();
    }

    /**
     * @param StockAllocation $alloc
     * @param RequirementCollection $collection
     * @return bool
     *  True if there is a conflict between the allocation and any
     *  of the consumers in the collection.
     */
    public function isConflict(StockAllocation $alloc, RequirementCollection $collection)
    {
        /* Delivered allocations don't hurt nobody. */
        if ($alloc->isDelivered()) {
            return false;
        }

        $this->validateAllocation($alloc);
        foreach ($collection->getRequirements() as $requirement) {
            if ($this->consumerConflicts($alloc, $requirement, $collection->isShareBins())) {
                return true;
            }
        }
        return false;
    }

    private function validateAllocation(StockAllocation $alloc)
    {
        if (! $this->validator->isValid($alloc)) {
            throw new InvalidAllocationException($alloc, $this->validator->getMessages());
        }
    }

    private function consumerConflicts(
        StockAllocation $alloc,
        Requirement $requirement,
        $shareBins)
    {
        if ($requirement->isForSameOrder($alloc)) {
            return false; // no conflict
        }
        $shareBins = $shareBins || $alloc->isForMissingStock();
        if (! $shareBins) {
            return true;
        }
        return ! $this->areAtSameLocation($alloc, $requirement);
    }

    private function areAtSameLocation(StockAllocation $alloc, Requirement $requirement)
    {
        $aLocation = $alloc->getRequirement()->getFacility();
        $cLocation = $requirement->getFacility();
        return $cLocation->equals($aLocation);
    }
}
