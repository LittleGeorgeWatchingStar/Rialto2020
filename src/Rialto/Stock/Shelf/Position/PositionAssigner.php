<?php

namespace Rialto\Stock\Shelf\Position;

use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Shelf\Position\Query\FirstAvailablePositionQuery;
use Rialto\Stock\Shelf\ShelfPosition;
use Rialto\Stock\Shelf\Velocity;
use Rialto\Stock\Shelf\Velocity\VelocityCalculator;


/**
 * Assigns bins that arrive at a facility to a shelf position.
 *
 * The position is typically determined by the "velocity" of the item
 * and the shelf positions that are available.
 */
class PositionAssigner
{
    /**
     * @var DbManager
     */
    private $dbm;

    /**
     * @var VelocityCalculator
     */
    private $velocity;

    /**
     * @var FirstAvailablePositionQuery
     */
    private $positionQuery;

    public function __construct(DbManager $dbm,
                                VelocityCalculator $velocity,
                                FirstAvailablePositionQuery $positionQuery)
    {
        $this->dbm = $dbm;
        $this->velocity = $velocity;
        $this->positionQuery = $positionQuery;
    }

    /**
     * Assigns shelf positions to as many bins as possible.
     *
     * @param StockBin[] $bins
     * @return StockBin[] The subset of $bins that were actually updated.
     */
    public function assignPositions(array $bins)
    {
        $modified = [];
        foreach ($bins as $bin) {
            if (!$bin->hasShelfPosition()) {
                $this->assignPosition($bin);
                if ($bin->hasShelfPosition()) {
                    // assignPosition() might have failed.
                    $modified[] = $bin;
                }
            }
        }
        return $modified;
    }

    /**
     * Assigns a shelf position to $bin, if a shelf position is available.
     *
     * @param StockBin $bin
     * @param Velocity|null $velocity If not null, forces the assigner to
     * find a shelf of the given velocity.
     */
    public function assignPosition(StockBin $bin, Velocity $velocity = null)
    {
        if ($bin->isEmpty()) {
            return; // no need to shelve empty bins
        }
        if (null === $velocity) {
            $velocity = $this->velocity->getVelocity($bin, $bin->getFacility());
        }
        $position = $this->findFirstAvailablePosition($bin, $velocity);
        if ($position) {
            $bin->setShelfPosition($position);
            /* We have to flush so any subsequent queries have the most
            recent assignments. Slow, perhaps, but simple. */
            $this->dbm->flush();
        }
    }

    /**
     * @return ShelfPosition|null
     */
    private function findFirstAvailablePosition(StockBin $bin, Velocity $velocity)
    {
        return ($this->positionQuery)($bin, $velocity);
    }

    /**
     * @param StockBin[] $bins
     * @return StockBin[] The subset of $bins that were actually updated.
     */
    public function unassignPositions(array $bins)
    {
        $modified = [];
        foreach ($bins as $bin) {
            if ($bin->hasShelfPosition()) {
                $bin->clearShelfPosition();
                $modified[] = $bin;
            }
        }
        return $modified;
    }
}
