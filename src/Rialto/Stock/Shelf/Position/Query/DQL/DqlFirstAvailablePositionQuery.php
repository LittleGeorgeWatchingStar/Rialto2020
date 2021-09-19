<?php


namespace Rialto\Stock\Shelf\Position\Query\DQL;


use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Shelf\Position\PositionQueryBuilder;
use Rialto\Stock\Shelf\Position\Query\FirstAvailablePositionQuery;
use Rialto\Stock\Shelf\Velocity;

/**
 * A first available position query specifically tailored to a doctrine
 * @see EntityRepository
 */
final class DqlFirstAvailablePositionQuery implements FirstAvailablePositionQuery
{
    /** @var DbManager */
    private $dbm;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(StockBin $bin, Velocity $velocity)
    {
        $builder = new PositionQueryBuilder($this->dbm);
        return $builder
            ->canAccomodateBin($bin)
            ->isUnoccupied()
            ->byVelocity($velocity)
            ->orderByCoordinates()
            ->getFirstResultOrNull();
    }
}
