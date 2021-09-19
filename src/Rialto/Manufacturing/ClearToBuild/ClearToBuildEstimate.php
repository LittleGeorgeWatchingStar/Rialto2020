<?php

namespace Rialto\Manufacturing\ClearToBuild;

use DateTime;

class ClearToBuildEstimate
{
    /**
     * @var DateTime|null
     */
    private $estimateDate;

    /**
     * Is already clear to build.
     * @var bool
     */
    private $isClearToBuild;

    /**
     * If is already clear to build or estimate is available.
     */
    private $isAvailable;

    public function __construct()
    {
        $this->estimateDate = null;
        $this->isClearToBuild = true;
        $this->isAvailable = true;
    }

    /**
     * Returns null if it is already clear to build or estimate is not available.
     * @return DateTime|null
     */
    public function getEstimateDate()
    {
        return $this->isAvailable ? $this->estimateDate : null;
    }

    /**
     * @param DateTime|null $estimateDate
     */
    public function setEstimateDate($estimateDate): void
    {
        $this->isClearToBuild = false;
        if (!$estimateDate) {
            $this->isAvailable = false;
        } else if ($estimateDate > $this->estimateDate) {
            $this->estimateDate = $estimateDate;
        }
    }

    /**
     * If is already clear to build or estimate is available.
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function aggregate(ClearToBuildEstimate $other): ClearToBuildEstimate
    {
        $newEstimate = new ClearToBuildEstimate();

        if (!$this->isAvailable || !$other->isAvailable) {
            $newEstimate->setEstimateDate(null);
        } else {
            if ($this->getEstimateDate()) {
                $newEstimate->setEstimateDate($this->getEstimateDate());
            }
            if ($other->getEstimateDate()) {
                $newEstimate->setEstimateDate($other->getEstimateDate());
            }
        }

        return $newEstimate;
    }

}