<?php

namespace Rialto\Manufacturing\ClearToBuild;

use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\CommitmentDateEstimator\StockProducerCommitmentDateEstimator;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Shipping\Method\ShippingTimeEstimator\ShippingTimeEstimatorInterface;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Transfer\Transfer;

class ClearToBuildFactory
{
    /** @var StockProducerCommitmentDateEstimator */
    private $commitmentDateEstimator;

    /** @var ShippingTimeEstimatorInterface */
    private $shippingTimeEstimator;

    public function __construct(StockProducerCommitmentDateEstimator $commitmentDateEstimator,
                                ShippingTimeEstimatorInterface $shippingTimeEstimator)
    {
        $this->commitmentDateEstimator = $commitmentDateEstimator;
        $this->shippingTimeEstimator = $shippingTimeEstimator;
    }

    /**
     * @return ClearToBuildEstimate
     */
    public function getEstimateForPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $clearToBuild = new ClearToBuildEstimate();

        foreach ($purchaseOrder->getWorkOrders() as $workOrder) {
            foreach ($workOrder->getRequirements() as $requirement) {
                $requirementClearToBuild = $this->getRequirementClearToBuildEstimate($requirement);
                if (!$requirementClearToBuild->isAvailable()) {
                    return $requirementClearToBuild;
                }
                $clearToBuild = $clearToBuild->aggregate($requirementClearToBuild);
            }
        }
        return $clearToBuild;
    }

    private function getRequirementClearToBuildEstimate(Requirement $requirement): ClearToBuildEstimate
    {
        $clearToBuild = new ClearToBuildEstimate();

        if ($requirement->isProvidedByChild()) {
            return $clearToBuild;
        }

        if ($requirement->getTotalQtyUnallocated() <= 0) {
            foreach ($requirement->getAllocations() as $allocation) {
                $allocationClearToBuild = $this->getAllocationClearToBuildEstimate($allocation);
                if (!$allocationClearToBuild->isAvailable()) {
                    return $allocationClearToBuild;
                }
                $clearToBuild = $clearToBuild->aggregate($allocationClearToBuild);
            }
            return $clearToBuild;
        }

        $clearToBuild->setEstimateDate(null);
        return $clearToBuild;
    }

    private function getAllocationClearToBuildEstimate(StockAllocation $allocation): ClearToBuildEstimate
    {
        $source = $allocation->getSource();
        if ($source instanceof StockBin) {
            return  $this->getStockBinClearToBuildEstimate($source, $allocation);
        } else if ($source instanceof StockProducer) {
            return  $this->getStockProducerClearToBuildEstimate($source, $allocation);
        }

        throw new \UnexpectedValueException(sprintf(
            'Unexpected stock source type %s', get_class($source)
        ));
    }

    private function getStockBinClearToBuildEstimate(StockBin $bin,
                                                     StockAllocation $allocation): ClearToBuildEstimate
    {
        $clearToBuild = new ClearToBuildEstimate();

        if ($allocation->isWhereNeeded()) {
            return $clearToBuild;
        }

        if ($bin->isInTransit()) {
            /** @var $transfer Transfer */
            $transfer = $bin->getLocation();
            $isInTransitToWhereNeeded = $transfer->isDestinedFor($allocation->getLocationWhereNeeded());
            if ($isInTransitToWhereNeeded && $transfer->getDateShipped() && $transfer->getShippingMethod()) {
                $estimatedArrivalDate = $this->shippingTimeEstimator->getEstimatedDeliveryDate($transfer->getShippingMethod(), $transfer->getDateShipped());
                if ($estimatedArrivalDate) {
                    $clearToBuild->setEstimateDate($estimatedArrivalDate);
                    return $clearToBuild;
                }
            }
        }

        $clearToBuild->setEstimateDate(null);
        return $clearToBuild;
    }

    private function getStockProducerClearToBuildEstimate(StockProducer $producer,
                                                          StockAllocation $allocation): ClearToBuildEstimate
    {
        $clearToBuild = new ClearToBuildEstimate();

        if ($allocation->isWhereNeeded()) {
            return $clearToBuild;
        }

        if ($producer->getCommitmentDate()) {
            $clearToBuild->setEstimateDate($producer->getCommitmentDate());
            return $clearToBuild;
        }

        if ($producer->getRequestedDate()) {
            $clearToBuild->setEstimateDate($producer->getRequestedDate());
            return $clearToBuild;
        }

        $estimatedCommitmentDate = $this->commitmentDateEstimator->getEstimatedCommitmentDate($producer);
        if ($estimatedCommitmentDate) {
            $clearToBuild->setEstimateDate($estimatedCommitmentDate);
            return $clearToBuild;
        }

        $clearToBuild->setEstimateDate(null);
        return $clearToBuild;
    }
}