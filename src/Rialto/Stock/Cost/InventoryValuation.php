<?php

namespace Rialto\Stock\Cost;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class InventoryValuation
{
    /** @var DoctrineDbManager */
    private $dbm;

    /** @var Connection */
    private $conn;

    /**
     * @var StockCategory[]
     * @Assert\Count(min=1, minMessage="Please select at least one category.")
     */
    private $categories;

    private $location = null;

    private $date;

    private $showZeroes = false;

    public function __construct(DoctrineDbManager $dbm)
    {
        $this->dbm = $dbm;
        $this->conn = $dbm->getEntityManager()->getConnection();
        $this->date = new \DateTime();
        $this->categories = new ArrayCollection([
            $dbm->need(StockCategory::class, StockCategory::PART),
        ]);
    }

    /** @return Collection */
    public function getCategories()
    {
        return $this->categories;
    }

    public function addCategory(StockCategory $category)
    {
        $this->categories[] = $category;
    }

    public function removeCategory(StockCategory $category)
    {
        $this->categories->removeElement($category);
    }

    public function setCategories(array $categories)
    {
        $this->categories = new ArrayCollection($categories);
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation(Facility $location = null)
    {
        $this->location = $location;
    }

    public function getDate()
    {
        return clone $this->date;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = clone $date;
    }

    public function isShowZeroes()
    {
        return $this->showZeroes;
    }

    public function setShowZeroes($show)
    {
        $this->showZeroes = (bool) $show;
    }

    /** @return CategoryValuation[] */
    public function getCategoryValuations()
    {
        $categories = [];
        foreach ( $this->categories as $category ) {
            $valuation = new CategoryValuation($category);
            $items = $this->getItemValuationsByCategory($category);
            $valuation->setItems($items);
            $categories[] = $valuation;
        }
        return $categories;
    }

    /** @return ItemValuation[] */
    private function getItemValuationsByCategory(StockCategory $category)
    {
        $qb = $this->conn->createQueryBuilder();
        $qb->select('i.StockID as stockCode')
            ->addSelect('i.Description as description')
            ->addSelect('ifnull(sum(m.quantity), 0) as quantity')
//            ->addSelect('gri.dateReceived')
            ->from('StockMaster', 'i')
            ->join('i', 'Locations', 'l', true)
            ->leftJoin('i', 'StockMove', 'm',
                'm.stockCode = i.StockID
                and m.locationID = l.LocCode
                and date(m.dateMoved) <= ?
                and m.binID is not null')
//            ->join('i', 'SuppInvoiceDetails', 'invoiceItem',
//                'invoiceItem.StockID = i.StockID')
//            ->join('invoiceItem', 'GoodsReceivedItem', 'gri',
//                'gri.invoiceItemID = invoiceItem.SIDetailID')
            ->where('i.CategoryID = ?')
            ->andWhere('i.MBflag not in (?)')
            ->andWhere('(i.PhaseOut is null or i.PhaseOut > ?)')
            ->groupBy('i.StockID');
        if (! $this->showZeroes ) {
            $qb->having('quantity != 0');
        }

        $params = [
            $this->date->format('Y-m-d'),
            $category->getId(),
            [StockItem::ASSEMBLY, StockItem::DUMMY],
            $this->date->format('Y-m-d'),
        ];
        $types = [
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            Connection::PARAM_STR_ARRAY,
            \PDO::PARAM_STR,
        ];
        if ( $this->location ) {
            $qb->andWhere('l.LocCode = ?');
            $params[] = $this->location->getId();
            $types[] = \PDO::PARAM_STR;
        }
        $sql = $qb->getSQL();
        $stmt = $this->conn->executeQuery($sql, $params, $types);

        $items = [];
        while ( $fields = $stmt->fetch(\PDO::FETCH_ASSOC) ) {
            $item = new ItemValuation($fields);
            $item->loadStandardCost($this->dbm, $this->date);
            $item->loadMostRecentPurchaseOrderItem($this->dbm);
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @param CategoryValuation[] $byCategory
     * @return \SplObjectStorage
     */
    public function getAccountTotals(array $byCategory)
    {
        $byAccount = new \SplObjectStorage();
        foreach ( $byCategory as $valuation ) {
            $account = $valuation->getStockAccount();
            $amount = isset($byAccount[$account]) ? $byAccount[$account] : 0;
            $amount += $valuation->getTotalValue();
            $byAccount[$account] = $amount;
        }
        return $byAccount;
    }
}
