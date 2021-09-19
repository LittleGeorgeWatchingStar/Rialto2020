<?php

namespace Rialto\Accounting\Web\Report;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Cost\InventoryValuation;
use Rialto\Web\Report\AuditColumn;
use Rialto\Web\Report\AuditTable;

/**
 *
 */
class InventoryValuationTaxAudit implements AuditTable
{
    /** @var GLAccount */
    private $stockAccount;

    private $results = [];
    private $columns = [];

    public function __construct(GLAccount $stockAccount)
    {
        $this->stockAccount = $stockAccount;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getDescription()
    {
        return '';
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getTitle()
    {
        return $this->stockAccount->getName();
    }

    public function getKey()
    {
        return str_replace(' ', '_', $this->getTitle());
    }

    public function loadResults(DoctrineDbManager $dbm, array $params)
    {
        $valuation = new InventoryValuation($dbm);
        $valuation->setCategories($this->getCategories($dbm));
        $valuation->setDate(new \DateTime($params['yearEnd']));
        $valuation->setShowZeroes(false);

        $this->results = [];
        foreach ( $valuation->getCategoryValuations() as $cat ) {
            foreach ( $cat->getItems() as $item ) {
                $this->results[] = [
                    'StockCode' => $item->getSku(),
                    'Description' => $item->getDescription(),
                    'OnHand' => $item->getQuantity(),
                    'StdCost' => $item->getStandardCost(),
                    'Updated' => $item->getLastUpdated(),
                    'EndDate' => $params['yearEnd'],
                    'Amount' => $item->getTotalValue(),
                ];
            }
        }

        $this->columns = [];
        $col = $this->createColumn('StockCode');

        $col = $this->createColumn('Description');
        $col->setWidth('6cm');

        $col = $this->createColumn('OnHand');
        $col->setScale(0);

        $col = $this->createColumn('StdCost');
        $col->setScale(4);

        $col = $this->createColumn('EndDate');

        $col = $this->createColumn('Amount');
        $col->setScale(2);
    }

    private function createColumn($key)
    {
        $col = new AuditColumn($key);
        $this->columns[$key] = $col;
        return $col;
    }

    private function getCategories(DbManager $dbm)
    {
        $repo = $dbm->getRepository(StockCategory::class);
        return $repo->findBy([
            'stockAccount' => $this->stockAccount->getId()
        ]);
    }

    public function getTotal()
    {
        $column = $this->columns['Amount'];
        return $column->getTotal($this->results);
    }

}
