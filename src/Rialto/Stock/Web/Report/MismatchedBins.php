<?php

namespace Rialto\Stock\Web\Report;

use Doctrine\DBAL\Query\QueryBuilder;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Facility\Facility;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\SqlQueryBuilderAudit;

/**
 * Shows bins whose stock move quantities don't add up to match the bin quantity.
 */
class MismatchedBins extends BasicAuditReport
{
    private $locations;

    /** @var QueryBuilder */
    private $qb;

    public function init(DbManager $dbm, array $params)
    {
        $repo = $dbm->getRepository(Facility::class);
        $this->locations = $repo->findAll();
        $this->qb = $dbm->getConnection()->createQueryBuilder();
    }

    public function getLocations()
    {
        return $this->locations;
    }

    protected function getDefaultParameters(array $query): array
    {
        return [
            'binLocation' => null,
            'moveLocation' => null,
        ];
    }

    public function getTables(array $params): array
    {
        $reports = [];

        $this->qb->select('bin.StockID as stockCode')
            ->addSelect('bin.SerialNo as binID')
            ->addSelect('bin.Quantity as binQty')
            ->addSelect('sum(ifnull(move.quantity, 0)) as moveQty')
            ->from('StockSerialItems', 'bin')
            ->leftJoin('bin', 'StockMove', 'move', 'bin.SerialNo = move.binID')
            ->groupBy('bin.SerialNo')
            ->having('binQty != moveQty')
            ->orderBy('bin.StockID', 'asc')
            ->addOrderBy('bin.SerialNo', 'asc');

        if (! empty($params['binLocation']) ) {
            $this->qb->andWhere('bin.LocCode = :binLocation');
        }
        if (! empty($params['moveLocation']) ) {
            $this->qb->andWhere('move.locationID = :moveLocation');
        }

        $report = new SqlQueryBuilderAudit(
            "Bins whose stock move quantities don't match bin quantites",
            $this->qb);

        $report->setScale('binQty', 0);
        $report->setScale('moveQty', 0);

        $reports[] = $report;

        return $reports;
    }
}
