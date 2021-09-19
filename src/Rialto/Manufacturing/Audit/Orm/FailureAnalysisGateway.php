<?php

namespace Rialto\Manufacturing\Audit\Orm;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Manufacturing\Audit\AuditFailureAnalysis;
use Rialto\Manufacturing\Requirement\Orm\RequirementRepository;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;

/**
 * Locates records needed to explain why an audit adjustment could not be
 * made.
 *
 * @see AuditFailureAnalysis
 */
class FailureAnalysisGateway
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Requirements for other work orders that are higher-priority than ours.
     *
     * @return Requirement[]
     */
    public function findCompetitors(RequirementCollection $requirements)
    {
        /** @var $repo RequirementRepository */
        $repo = $this->om->getRepository(Requirement::class);
        return $repo->findHigherPriorityCompetitors($requirements);
    }

    /**
     * Bins that are available to our requirements at the CM.
     *
     * @return StockBin[]
     */
    public function findBins(RequirementCollection $requirements)
    {
        /** @var $repo StockBinRepository */
        $repo = $this->om->getRepository(StockBin::class);
        return $repo->createBuilder()
            ->available()
            ->byRequirement($requirements)
            ->atFacility($requirements->getFacility())
            ->getResult();
    }
}
