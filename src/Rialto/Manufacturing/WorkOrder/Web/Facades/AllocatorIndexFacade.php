<?php

namespace Rialto\Manufacturing\WorkOrder\Web\Facades;

use Rialto\Manufacturing\Allocation\AllocatorIndex;
use Rialto\Manufacturing\Allocation\WorkOrderAllocatorGroup;
use Rialto\Stock\Facility\Facility;
use Twig\Environment;

class AllocatorIndexFacade
{
    /** @var AllocatorIndex */
    private $allocatorIndex;

    /** @var WorkOrderAllocatorGroup[] */
    private $group = [];

    /** @var Environment */
    private $twig;

    public function __construct(AllocatorIndex $index, Environment $twig)
    {
        $this->allocatorIndex = $index;

        $this->twig = $twig;

        $this->group = $index->getGroups();
    }

    public function getLocationsName(WorkOrderAllocatorGroup $group)
    {
        $locations = $group->getLocations();
        return array_map(function (Facility $facility) {
            return [
                'facilityName' => $facility->getName(),
            ];
        }, $locations);
    }

    public function getTotalFromLocations(WorkOrderAllocatorGroup $group)
    {
        return $group->getTotalFromLocations();
    }

    public function getTotalStillNeeded(WorkOrderAllocatorGroup $group)
    {
        return $group->getTotalStillNeeded();
    }

    public function getTotalFromOrders(WorkOrderAllocatorGroup $group)
    {
        return $group->getTotalFromOrders();
    }

}