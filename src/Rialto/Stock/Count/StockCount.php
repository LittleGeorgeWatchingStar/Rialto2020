<?php

namespace Rialto\Stock\Count;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\User\User;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Move\StockMove;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A record of an inventory count performed at a location.
 *
 * A stock count has three main lifecycle events:
 * 1) Request - the admin requests that a stock count be done.
 * 2) Entry - the location manager enters the stock counts.
 * 3) Approval - the admin reviews the counts and approves or rejects them.
 *
 * Actual stock adjustments are not done until the Approval stage.
 */
class StockCount implements RialtoEntity
{
    /** @var integer */
    private $id;

    /**
     * The location where the count should be performed.
     * @var Facility
     * @Assert\NotNull
     */
    private $location;

    /** @var User */
    private $requestedBy;

    /**
     * The counts entered for each bin.
     * @var BinCount[]
     * @Assert\Count(min=1, minMessage="No matching bins were found.")
     * @Assert\Valid(traverse=true)
     */
    private $binCounts = [];

    /**
     * When this stock count was originally requested.
     * @var DateTime
     */
    private $dateRequested;

    public function __construct(User $requestedBy)
    {
        $this->requestedBy = $requestedBy;
        $this->dateRequested = new DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Facility $location
     */
    public function setLocation(Facility $location)
    {
        $this->location = $location;
    }

    /**
     * @return Supplier|null
     */
    public function getSupplier()
    {
        return $this->location->getSupplier();
    }

    /** @return User */
    public function getRequestedBy()
    {
        return $this->requestedBy;
    }

    /** @return bool */
    public function isRequestedBy(User $user)
    {
        return $this->requestedBy->isEqualTo($user);
    }

    public function getBinCounts()
    {
        return $this->binCounts;
    }

    public function addBin(StockBin $bin = null)
    {
        if ($bin) {
            $binCount = new BinCount($this, $bin);
            $this->binCounts[] = $binCount;
        }
    }

    public function removeBin(StockBin $bin)
    {
        $this->binCounts = array_filter($this->binCounts, function (BinCount $c) use ($bin) {
            return ! $c->isBin($bin);
        });
    }

    /** @return StockBin[] */
    public function getBins()
    {
        return array_map(function (BinCount $c) {
            return $c->getBin();
        }, $this->binCounts);
    }

    public function getDateRequested()
    {
        return $this->dateRequested;
    }

    public function hasCounts()
    {
        foreach ($this->binCounts as $binCount) {
            if ($binCount->isCounted()) {
                return true;
            }
        }
        return false;
    }

    public function loadStockMoveHistory(ObjectManager $dbm)
    {
        $repo = $dbm->getRepository(StockMove::class);
        foreach ($this->binCounts as $binCount) {
            $binCount->loadStockMoveHistory($repo);
        }
    }

    public function applySelectedAllocations()
    {
        foreach ($this->binCounts as $binCount) {
            $binCount->applySelectedAllocations();
        }
    }

    /**
     * Approves the count and makes any required stock adjustments.
     *
     * @param StockAdjustment $adjustment Any stock changes will
     *   be created via this adjustment.
     */
    public function approve(StockAdjustment $adjustment)
    {
        foreach ($this->binCounts as $binCount) {
            $binCount->approve($adjustment);
        }
    }

    /** @return string */
    public function getMemo()
    {
        return sprintf('Stock count ID %s at %s',
            $this->id,
            $this->location);
    }
}
