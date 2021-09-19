<?php

namespace Rialto\Manufacturing\Audit;

use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Manufacturing\Audit\Orm\FailureAnalysisGateway;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\Bin\StockBin;

/**
 * If we can't make the adjustment requested in a work order audit,
 * this class figures out why.
 */
class AuditFailureAnalysis
{
    /** @var RequirementCollection */
    private $requirements;

    /** @var Requirement[] */
    private $competitors;

    /** @var  StockBin[] */
    private $bins;

    public function __construct(RequirementCollection $requirements,
                                FailureAnalysisGateway $gateway)
    {
        $this->requirements = $requirements;
        $this->competitors = $gateway->findCompetitors($requirements);
        $this->bins = $gateway->findBins($requirements);
    }

    public function getFullSku()
    {
        return $this->requirements->getFullSku();
    }

    public function getFacility()
    {
        return $this->requirements->getFacility();
    }

    /**
     * @deprecated
     */
    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFacility();
    }

    /**
     * @return StockBin[]
     */
    public function getBins()
    {
        return $this->bins;
    }

    /**
     * @return PurchaseOrder[]
     */
    public function getCompetitors()
    {
        $orders = [];
        foreach ($this->competitors as $requirement) {
            $wo = $requirement->getWorkOrder();
            $po = $wo->getPurchaseOrder();
            $orders[$po->getId()] = $po;
        }
        return $orders;
    }

    public function getSummary()
    {
        $summary = [];
        $c = $this->getCompetitors();
        if (count($c) > 0) {
            $summary[] = "competitors: " . join(', ', array_keys($c));
        }
        if (count($this->bins) > 0) {
            $summary[] = "available bins: "
                . join(', ', array_map(function (StockBin $b) {
                    return $b->getLabelWithQuantity();
                }, $this->bins));
        } else {
            $summary[] = "no available bins";
        }
        return join('; ', $summary);
    }
}
