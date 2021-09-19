<?php

namespace Rialto\Purchasing\LeadTime;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Bom\Orm\BomItemRepository;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Level\StockLevelService;


/**
 * Database access for @see LeadTimeCalculator
 */
class LeadTimeGateway
{
    /** @var PurchasingDataRepository */
    private $purchDataRepo;

    /** @var BomItemRepository */
    private $bomItemRepo;

    /** @var StockLevelService */
    private $stockLevels;

    public function __construct(ObjectManager $dbm, StockLevelService $stockLevels)
    {
        $this->purchDataRepo = $dbm->getRepository(PurchasingData::class);
        $this->bomItemRepo = $dbm->getRepository(BomItem::class);
        $this->stockLevels = $stockLevels;
    }

    /**
     * @return PurchasingData|null
     */
    public function findPurchasingData(StockItem $item, Version $version, $orderQty)
    {
        return $this->purchDataRepo->findPreferredByVersion($item, $version, $orderQty);
    }

    /** @return BomItem[] */
    public function getConsignmentBom(PurchasingData $purchData, Version $version)
    {
        return $this->bomItemRepo->findConsignmentComponents($purchData, $version);
    }

    public function isInStock(PhysicalStockItem $item, Version $version, $orderQty): bool
    {
        $stockLevel = $this->stockLevels->getTotalQtyUnallocated($item, $version);
        return $stockLevel >= $orderQty;
    }
}
