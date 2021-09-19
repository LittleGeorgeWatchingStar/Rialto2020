<?php

namespace Rialto\Allocation\Status;

class AllocationStatusString
{
    const REQUIRES_ALLOCATIONS = 'Requires Allocation';
    const FULLY_ALLOCATED = 'Fully Allocated';
    const READY_TO_KIT = 'Ready to Kit';
    const IN_TRANSIT = 'In Transit to Location';
    const KIT_COMPLETE = 'Kit Complete';

    /** @var AllocationStatus */
    private $allocationStatus;

    public function __construct(AllocationStatus $allocationStatus)
    {
        $this->allocationStatus = $allocationStatus;
    }

    public function __toString()
    {
        if ($this->allocationStatus->isKitComplete()) {
            return self::KIT_COMPLETE;
        } else if ($this->allocationStatus->isEnRoute()) {
            return self::IN_TRANSIT;
        } else if ($this->allocationStatus->isReadyToKit()) {
            return self::READY_TO_KIT;
        } else if ($this->allocationStatus->isFullyAllocated()) {
            return self::FULLY_ALLOCATED;
        } else {
            return self::REQUIRES_ALLOCATIONS;
        }
    }
}

