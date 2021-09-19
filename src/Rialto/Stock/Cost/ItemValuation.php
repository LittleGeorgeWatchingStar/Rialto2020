<?php

namespace Rialto\Stock\Cost;

use DateTime;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Order\Orm\PurchaseOrderItemRepository;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Stock\Cost\Orm\StandardCostRepository;
use Rialto\Stock\Item;

/**
 * Stores the quantity and value of a stock item on a given date.
 *
 * @property string stockCode
 * @property string description
 * @property int quantity
 */
class ItemValuation implements Item
{
    private $fields = [];

    private $standardCost;

    /** @var DateTime|null */
    private $lastUpdated;

    /** @var PurchaseOrderItem */
    private $poItem;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function getSku()
    {
        return $this->stockCode;
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getQuantity()
    {
        return (int) $this->quantity;
    }

    public function loadStandardCost(DbManager $dbm, DateTime $date)
    {
        /** @var $repo StandardCostRepository */
        $repo = $dbm->getRepository(StandardCost::class);
        $stdCost = $repo->findByItemAndDate($this, $date);
        $this->standardCost = $stdCost ? $stdCost->getTotalCost() : null;
        $this->lastUpdated = $stdCost ? $stdCost->getDate() : null;
    }

    public function getStandardCost()
    {
        return $this->standardCost;
    }

    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    public function loadMostRecentPurchaseOrderItem(DbManager $dbm)
    {
        /** @var PurchaseOrderItemRepository $repo */
        $repo = $dbm->getRepository(PurchaseOrderItem::class);
        $recent = $repo->createQueryBuilder('poItem')
            ->join('poItem.purchasingData', 'pd')
            ->where('pd.stockItem = :stockCode')
            ->setParameter('stockCode', $this->getSku())
            ->orderBy('poItem.dateClosed', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
        $this->poItem = $recent;
    }

    /**
     * @return DateTime|null
     */
    public function getMostRecentPoDate()
    {
        if ($this->poItem) {
            return $this->poItem->getDateClosed() ?: $this->poItem->getDateCreated();
        }

        return null;
    }

    /**
     * @return float|null
     */
    public function getMostRecentPoUnitCost()
    {
        if ($this->poItem) {
            if ($this->poItem->getActualCost() > 0) {
                return $this->poItem->getActualCost();
            }
            return $this->poItem->getExpectedUnitCost();
        }

        return null;
    }

    public function usingActualPoUnitCost(): bool
    {
        if ($this->poItem) {
            return $this->poItem->getActualCost() > 0;
        }

        return false;
    }

    public function __get($field)
    {
        if ( isset($this->fields[$field]) ) {
            return $this->fields[$field];
        }
        throw new \LogicException("ItemValuation has no field $field");
    }

    public function getTotalValue()
    {
        if (null === $this->standardCost ) {
            return null;
        }
        return $this->standardCost * $this->quantity;
    }
}
