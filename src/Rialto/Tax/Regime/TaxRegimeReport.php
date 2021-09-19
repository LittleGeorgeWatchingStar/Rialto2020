<?php

namespace Rialto\Tax\Regime;

use DateTime;
use Doctrine\DBAL\Connection;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Tax\Regime\Orm\TaxRegimeRepository;
use Rialto\Tax\TaxExemption;

/**
 * Report sales and sales taxes by tax regime.
 */
class TaxRegimeReport
{
    /** @var Connection */
    private $conn;

    /** @var TaxRegimeRepository */
    private $regimeRepo;

    private $index = [];

    public function __construct(DoctrineDbManager $dbm)
    {
        $this->conn = $dbm->getConnection();
        $this->regimeRepo = $dbm->getRepository(TaxRegime::class);
    }

    public function loadData(Period $start, Period $end)
    {
        $results = $this->fetchTransactionData($start, $end);
        $this->createItems($results, $end->getEndDate());
    }

    private function fetchTransactionData(
        Period $start,
        Period $end)
    {
        $sql = "SELECT lower(county.Name) AS county,
            lower(address.city) AS city,
            sum(t.OvAmount) AS sales,
            sum(t.OvGST) AS taxesPaid,
            sum(taxOwed.taxOwed) AS taxesOwed
            FROM DebtorTrans t
            JOIN DebtorsMaster customer
                ON t.customerID = customer.DebtorNo
            LEFT JOIN SalesOrders o
                ON o.OrderNo = t.Order_
            LEFT JOIN Geography_Address AS address
                ON o.shippingAddressID = address.id
            LEFT JOIN County county
                ON address.stateCode = 'CA' AND address.postalCode LIKE concat(county.PostalCode, '%')
            LEFT JOIN (
                SELECT sum(round(item.qtyInvoiced * item.finalUnitPrice, 2) * item.SalesTaxRate) AS taxOwed,
                item.OrderNo
                FROM SalesOrderDetails item
                GROUP BY item.OrderNo
            ) AS taxOwed ON taxOwed.OrderNo = o.OrderNo
            WHERE customer.StateStatus = :taxable
            AND address.countryCode IN ('US', 'United States')
            AND address.stateCode IN ('CA', 'California')
            AND t.Prd >= :startPeriod
            AND t.Prd <= :endPeriod
            AND t.Type IN (:invoice, :creditNote)
            GROUP BY county.Name, address.city
            ORDER BY county.Name, address.city";

        $params = [
            'startPeriod' => $start->getId(),
            'endPeriod' => $end->getId(),
            'invoice' => SystemType::SALES_INVOICE,
            'creditNote' => SystemType::CREDIT_NOTE,
            'taxable' => TaxExemption::NONE,
        ];
        $stmt = $this->conn->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }

    private function createItems(array $results, DateTime $date)
    {
        $this->index = [];

        foreach ($results as $result) {
            $matching = $this->regimeRepo->findByCountyAndCity(
                $result['county'],
                $result['city'],
                $date);

            $item = $this->add($matching);
            $item->sales += $result['sales'];
            $item->taxesPaid += $result['taxesPaid'];
            $item->taxesOwed += $result['taxesOwed'];
        }
    }

    public function add(array $regimes): ReportItem
    {
        $item = new ReportItem();
        foreach ($regimes as $regime) {
            $item->addRegime($regime);
        }

        $county = $item->county;
        $code = $item->regimeCode;
        if (!isset($this->index[$county][$code])) {
            $this->index[$county][$code] = $item;
        }
        return $this->index[$county][$code];
    }

    /**
     * @return ReportItem[]
     */
    public function getItems()
    {
        $items = [];
        foreach ($this->index as $subarray) {
            foreach ($subarray as $item) {
                $items[] = $item;
            }
        }
        usort($items, function (ReportItem $a, ReportItem $b) {
            return strcmp($a->county, $b->county) ?:
                strcmp($a->city, $b->city);
        });
        return $items;
    }
}
