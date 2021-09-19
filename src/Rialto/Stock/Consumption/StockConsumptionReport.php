<?php

namespace Rialto\Stock\Consumption;

use DateTime;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Bom\TurnkeyExclusion;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Sales\Order\Orm\SalesOrderDetailRepository;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Level\StockLevelService;

/**
 * Generates statistics about sales are driving the consumption rate of
 * purchased subcomponents.
 */
class StockConsumptionReport
{
    /** @var DbManager */
    private $dbm;

    /** @var StockLevelService */
    private $stockLevels;

    /** @var DateTime */
    private $startDate;

    /** @var DateTime */
    private $endDate;

    private $salesType = null;
    private $showTurnkey = false;
    private $index = null;

    public function __construct(DbManager $dbm, StockLevelService $stockLevels)
    {
        $this->dbm = $dbm;
        $this->stockLevels = $stockLevels;
        $this->setStartDate($this->getDefaultStartDate());
        $this->setEndDate($this->getDefaultEndDate());
    }

    private function getDefaultStartDate()
    {
        return new DateTime('-42 days');
    }

    private function getDefaultEndDate()
    {
        return new DateTime();
    }

    public function getStartDate()
    {
        return clone $this->startDate;
    }

    public function setStartDate(DateTime $startDate)
    {
        $this->startDate = clone $startDate;
        $this->startDate->setTime(0, 0, 0);
    }

    public function getEndDate()
    {
        return clone $this->endDate;
    }

    public function setEndDate(DateTime $endDate)
    {
        $this->endDate = clone $endDate;
        $this->endDate->setTime(23, 59, 59);
    }

    /** @return int */
    public function getDateDiff()
    {
        $diff = $this->startDate->diff($this->endDate);
        return $diff->days;
    }

    public function getSalesType()
    {
        return $this->salesType;
    }

    public function setSalesType(SalesType $salesType = null)
    {
        $this->salesType = $salesType;
    }

    public function isShowTurnkey()
    {
        return $this->showTurnkey;
    }

    public function setShowTurnkey($show)
    {
        $this->showTurnkey = $show;
    }

    public function getStatistics()
    {
        if (null === $this->index) {
            $this->loadStatistics();
        }
        return $this->getSortedIndex();
    }

    private function loadStatistics()
    {
        $this->index = [];
        $statistics = $this->fetchConsumptionStatistics();
        foreach ($statistics as $data) {
            $item = $data[0];
            $stat = new TopLevelStat($item, $data['version']);
            $this->addItemToIndex($stat, $data['totalQtyOrdered']);
        }
    }

    private function fetchConsumptionStatistics()
    {
        /** @var $repo SalesOrderDetailRepository */
        $repo = $this->dbm->getRepository(SalesOrderDetail::class);
        return $repo->findConsumptionStatistics($this->startDate, $this->endDate, $this->salesType);
    }

    private function addItemToIndex(
        Item $item,
        $parentQty,
        Item $parentItem = null)
    {
        if ($item->hasSubcomponents()) {
            $bom = $item->getBom();
            foreach ($bom as $bomItem) {
                $bomQty = $bomItem->getQuantity();
                $this->addItemToIndex($bomItem, $bomQty * $parentQty, $item);
            }
        } elseif ($item->isPurchased()) {
            if ($this->shouldBeIncluded($item, $parentItem)) {
                $this->addComponentToIndex($item, $parentQty, $parentItem);
            }
        }
    }

    private function shouldBeIncluded($component, $parentItem = null)
    {
        if (!$parentItem) {
            return (!$this->showTurnkey);
        }
        $purchData = $this->getPurchasingData($parentItem);
        if (!$purchData) {
            return (!$this->showTurnkey);
        }
        $isConsigned = $purchData->isTurnkey() ?
            TurnkeyExclusion::exists($parentItem, $component, $purchData->getBuildLocation()) :
            true;

        return ($this->showTurnkey xor $isConsigned);
    }

    /** @return PurchasingData */
    private function getPurchasingData($item)
    {
        /** @var $repo PurchasingDataRepository */
        $repo = $this->dbm->getRepository(PurchasingData::class);
        return $repo->findPreferredByVersion($item, $item->getVersion());
    }

    private function addComponentToIndex(Item $component, $qty, Item $parentItem = null)
    {
        $stockId = $component->getSku();
        $version = (string) $component->getVersion();
        if (!isset($this->index[$stockId][$version])) {
            $stat = new StockConsumptionStat($component);
            $stat->loadStockLevels($this->stockLevels);
            $stat->setNumOfDays($this->getDateDiff());
            $this->index[$stockId][$version] = $stat;
        }
        /** @var $stat StockConsumptionStat */
        $stat = $this->index[$stockId][$version];
        $stat->addQtyConsumed($qty);
        $stat->addParentItem($parentItem);
    }


    private function getSortedIndex()
    {
        $this->deepSort($this->index);
        return $this->index;
    }

    private function deepSort(array &$index)
    {
        uksort($index, "strnatcasecmp");
        foreach ($index as &$subindex) {
            if (is_array($subindex)) {
                $this->deepSort($subindex);
            }
        }
    }
}

/**
 * Used by StockConsumptionReport to recursively add subcomponents to the
 * index.
 */
class TopLevelStat implements Item
{
    private $item;

    function __construct(StockItem $item, $version)
    {
        $this->item = $item;
        if ($this->item->isVersioned()) {
            $this->version = new Version($version);
            if ($this->version->isAny()) {
                $this->version = $item->getShippingVersion();
            }
        } else {
            $this->version = Version::none();
        }
    }

    public function getStockItem()
    {
        return $this->item;
    }

    public function getSku()
    {
        return $this->item->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function hasSubcomponents()
    {
        return $this->item->hasSubcomponents();
    }

    public function getBom()
    {
        return $this->hasSubcomponents() ?
            $this->item->getBom($this->version) :
            null;
    }

    public function isPurchased()
    {
        return $this->item->isPurchased();
    }
}
