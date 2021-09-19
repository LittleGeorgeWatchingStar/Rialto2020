<?php

namespace Rialto\Stock\Level\Orm;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Rialto\Stock\Bin\HistorialStockBin;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;

/**
 * Finds stock levels as of a particular date.
 */
class HistoricalStockLevelRepository
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return HistorialStockBin[]
     */
    public function findBins(Facility $location, DateTime $asOf)
    {
        /* Find all moves at $location since $asOf. */
        $moves = "
            select binID, sum(quantity) as qtyDiff from StockMove
            where locationID = :location
            and dateMoved > :asOf
            group by binID
        ";

        /* Find all bins that are either currently at $location or have
         * moved to/from $location since $asOf. */
        $sql = "
            select bin.*
            , bin.Quantity - ifnull(moves.qtyDiff, 0) as qtyAsOf
            from StockSerialItems as bin
            left join ($moves) as moves on bin.SerialNo = moves.binID
            where (moves.qtyDiff is not null or bin.LocCode = :location)
            having qtyAsOf > 0
            order by bin.StockID, bin.SerialNo, qtyAsOf
        ";

        $rsm = new ResultSetMappingBuilder($this->em);
        $rsm->addRootEntityFromClassMetadata(StockBin::class, 'bin');
        $rsm->addScalarResult('qtyAsOf', 'qtyAsOf');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('location', $location);
        $query->setParameter('asOf', $asOf);
        return $this->hydrate($query->getResult());
    }

    /**
     * Convert the raw results array into a list of HistoricalStockBin objects.
     *
     * @param array $results
     * @return HistorialStockBin[]
     */
    private function hydrate(array $results)
    {
        return array_map(function(array $r) {
            return new HistorialStockBin($r[0], $r['qtyAsOf']);
        }, $results);
    }
}
