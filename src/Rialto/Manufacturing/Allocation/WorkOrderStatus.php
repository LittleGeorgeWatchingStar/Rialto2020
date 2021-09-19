<?php

namespace Rialto\Manufacturing\Allocation;

use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Status\ConsumerStatus;
use Rialto\Manufacturing\Requirement\Requirement as WorkOrderRequirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;

/**
 * Allocation status for a work order and its children.
 */
class WorkOrderStatus extends ConsumerStatus
{
    public function __construct(WorkOrder $wo)
    {
        parent::__construct($wo);
        if ($wo->hasChild()) {
            $this->addRequirements($wo->getChild());
        }
    }

    /**
     * @param WorkOrderRequirement $req
     */
    protected function addRequirement(Requirement $req)
    {
        /* If $req is provided by the child work order, then we
         * don't need to worry about whether it is allocated,
         * at location, etc. It will be provided automatically when
         * the child is received. */
        if (!$req->isProvidedByChild()) {
            parent::addRequirement($req);
        }
    }
}
