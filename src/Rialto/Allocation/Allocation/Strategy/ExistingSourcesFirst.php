<?php

namespace Rialto\Allocation\Allocation\Strategy;


use Rialto\Allocation\Allocation\AllocationStrategy;
use Rialto\Allocation\Requirement\RequirementCollection;

/**
 * No matter what allocation strategy we use, we should always allocate
 * first from sources that we're already using! Otherwise we have many
 * allocations from many sources where one allocation from one source would
 * do!
 */
class ExistingSourcesFirst implements AllocationStrategy
{
    /** @var AllocationStrategy */
    private $strategy;

    public function __construct(AllocationStrategy $innerStrategy)
    {
        $this->strategy = $innerStrategy;
    }

    public function getOptimalSources(RequirementCollection $consumers, array $sources)
    {
        $existing = $this->getExistingSources($consumers, $sources);
        $additional = $this->strategy->getOptimalSources($consumers, $sources);
        $result = array_merge($existing, $additional);
        return $result;
    }

    private function getExistingSources(RequirementCollection $consumers, array $sources)
    {
        $existing = [];
        foreach ($consumers->getRequirements() as $requirement) {
            foreach ($requirement->getAllocations() as $alloc) {
                $source = $alloc->getSource();
                if (in_array($source, $sources, true)) {
                    $existing[] = $source;
                }
            }
        }
        return $existing;
    }
}

