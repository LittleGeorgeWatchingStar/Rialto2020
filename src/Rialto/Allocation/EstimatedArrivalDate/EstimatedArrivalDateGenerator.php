<?php

namespace Rialto\Allocation\EstimatedArrivalDate;

use DateTime;
use Rialto\Allocation\AllocationInterface;
use Rialto\Purchasing\Producer\CommitmentDateEstimator\StockProducerCommitmentDateEstimator;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Shipping\Method\ShippingTimeEstimator\ShippingTimeEstimatorInterface;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Transfer\Transfer;

class EstimatedArrivalDateGenerator
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

    public function generate(AllocationInterface $allocation): ?DateTime
    {
        if ($allocation->isWhereNeeded()) {
            return null;
        }

        $source = $allocation->getSource();
        if ($source instanceof StockBin) {
            if ($source->isInTransit()) {
                /** @var $transfer Transfer */
                $transfer = $source->getLocation();
                $isInTransitToWhereNeeded = $transfer->isDestinedFor($allocation->getLocationWhereNeeded());
                if ($isInTransitToWhereNeeded && $transfer->getDateShipped() && $transfer->getShippingMethod()) {
                    $estimatedArrivalDate = $this->shippingTimeEstimator->getEstimatedDeliveryDate($transfer->getShippingMethod(), $transfer->getDateShipped());
                    return $estimatedArrivalDate;
                }
            }

        } else if ($source instanceof StockProducer) {
            $commitmentDate = $source->getCommitmentDate();
            $requestByDate = $source->getRequestedDate();

            if (!$commitmentDate && !$requestByDate) {
                $estimatedCommitmentDate = $this->commitmentDateEstimator->getEstimatedCommitmentDate($source);
                return $estimatedCommitmentDate;
            }
        }

        return null;
    }
}