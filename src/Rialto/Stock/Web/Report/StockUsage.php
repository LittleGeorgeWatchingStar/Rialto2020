<?php

namespace Rialto\Stock\Web\Report;

use Doctrine\DBAL\Query\QueryBuilder;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\DbManager;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\SqlQueryBuilderAudit;

/**
 * Shows stock usage by period.
 */
class StockUsage extends BasicAuditReport
{
    private $locations;

    /** @var QueryBuilder */
    private $qb;

    public function getAllowedRoles()
    {
        return [Role::STOCK];
    }

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
            '_limit' => 12,
            'location' => null,
            'types' => [
                SystemType::SALES_INVOICE,
                SystemType::CREDIT_NOTE,
                SystemType::WORK_ORDER_ISSUE,
            ],
            'stockCode' => null,
        ];
    }

    public function getTables(array $params): array
    {
        $reports = [];

        $this->qb->select('period.PeriodNo as periodNo')
            ->addSelect('period.LastDate_in_Period as endDate')
            ->addSelect('sum(-move.quantity) as qtyUsed')
            ->from('StockMove', 'move')
            ->join('move', 'Periods', 'period', 'move.periodID = period.PeriodNo')
            ->andWhere('move.systemTypeID in (:types)')
            ->andWhere('move.stockCode = :stockCode')
            ->groupBy('period.PeriodNo')
            ->orderBy('period.PeriodNo', 'desc')
            ->setMaxResults($params['_limit']);

        if (! empty($params['location']) ) {
            $this->qb->andWhere('move.locationID = :location');
        }

        $report = new SqlQueryBuilderAudit(
            "Stock usage",
            $this->qb);

        $report->setScale('qtyUsed', 0);

        $reports[] = $report;

        return $reports;
    }
}
