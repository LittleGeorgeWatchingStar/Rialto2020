<?php

namespace Rialto\Stock\Item\Version\Orm;


use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Web\StockItemPackage;
use Rialto\Stock\Sku;

/**
 * Database gateway for StockItemPackage
 *
 * @see StockItemPackage
 */
class StockItemPackageGateway
{
    /** @var DbManager */
    private $dbm;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    public function getCategory(): StockCategory
    {
        return StockCategory::fetchProduct($this->dbm);
    }

    public function getDefaultLabel(): StockItem
    {
        return $this->dbm->find(StockItem::class, Sku::PRINTED_LABEL);
    }

    /**
     * @return ItemVersion[]
     */
    public function findEligibleBoxes(): array
    {
        /** @var $repo ItemVersionRepository */
        $repo = $this->dbm->getRepository(ItemVersion::class);
        return $repo->findEligibleBoxes();
    }

    public function getWorkType(): WorkType
    {
        return WorkType::fetchPackage($this->dbm);
    }
}
