<?php

namespace Rialto\Manufacturing\Audit;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Manufacturing\Audit\Adjustment\AdjustmentStrategy;
use Rialto\Manufacturing\Audit\Orm\FailureAnalysisGateway;


/**
 * Adjusts stock allocations to conform to the results of a work order audit.
 */
class AuditAdjuster
{
    /** @var FailureAnalysisGateway */
    private $datasource;

    /** @var AdjustmentStrategy[] */
    private $release = [];

    /** @var AdjustmentStrategy[] */
    private $acquire = [];

    public function __construct(ObjectManager $om)
    {
        $this->datasource = new FailureAnalysisGateway($om);
    }

    public function addReleaseStrategy(AdjustmentStrategy $release)
    {
        $this->release[] = $release;
    }

    public function addAcquireStrategy(AdjustmentStrategy $acquire)
    {
        $this->acquire[] = $acquire;
    }

    public function adjustAllocations(AuditItem $item)
    {
        if ($item->getAdjustment() == 0) {
            return true;
        } elseif ($item->getAdjustment() < 0) {
            $this->releaseAllocations($item);
        } elseif ($item->getAdjustment() > 0) {
            $this->acquireAllocations($item);
        }

        $success = $item->isSuccessful();
        if (!$success) {
            $item->setFailureAnalysis(
                new AuditFailureAnalysis($item, $this->datasource));
        }
        return $success;
    }

    private function releaseAllocations(AuditItem $item)
    {
        foreach ($this->release as $strategy) {
            $strategy->releaseFrom($item);
        }
    }

    private function acquireAllocations(AuditItem $item)
    {
        foreach ($this->acquire as $strategy) {
            $strategy->acquireFor($item);
        }
    }
}
