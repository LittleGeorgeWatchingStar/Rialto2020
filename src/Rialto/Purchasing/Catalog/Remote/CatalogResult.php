<?php

namespace Rialto\Purchasing\Catalog\Remote;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\PurchasedStockItem;
use Symfony\Component\Validator\Constraints as Assert;

class CatalogResult
{
    /**
     * @var PurchasedStockItem
     *
     * @Assert\Valid
     */
    private $item;

    /**
     * @var PurchasingData[]
     *
     * @Assert\Valid(traverse=true)
     * @Assert\Count(
     *     min=1, minMessage="At least one purchasing data record is required.",
     *     max=10, maxMessage="At most {{ limit }} purchasing data records are allowed.")
     */
    private $purchData = [];

    /**
     * Matching purchasing data records that already exist.
     *
     * These are shown to the user to prevent duplicate stock items from
     * being created.
     *
     * @var PurchasingData[]
     */
    private $existing = [];

    public function __construct(PurchasedStockItem $item)
    {
        $this->item = $item;
    }

    public function __toString()
    {
        return sprintf('%s and %d purchasing data record(s)',
            $this->item,
            count($this->purchData));
    }

    /**
     * @return PurchasedStockItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * The default category is Part, but you can override it with this method.
     */
    public function setCategory(StockCategory $category)
    {
        $this->item->setCategory($category);
    }

    /** @return StockCategory */
    public function getCategory()
    {
        return $this->item->getCategory();
    }

    /**
     * @return PurchasingData[]
     */
    public function getPurchData()
    {
        return $this->purchData;
    }

    public function addPurchData(PurchasingData $pd = null)
    {
        if ($pd) {
            $this->purchData[] = $pd;
        }
    }

    public function hasPurchData()
    {
        return count($this->purchData) > 0;
    }

    public function setPurchData(array $purchData)
    {
        $this->purchData = $purchData;
    }

    public function loadExisting(ObjectManager $om)
    {
        /** @var $repo PurchasingDataRepository */
        $repo = $om->getRepository(PurchasingData::class);
        $this->existing = $repo->createBuilder()
            ->isDuplicateOfAny($this->purchData)
            ->getResult();
    }

    /**
     * @return PurchasingData[]
     */
    public function getExisting()
    {
        return $this->existing;
    }

    public function persistAll(ObjectManager $om)
    {
        $om->persist($this->item);
        foreach ($this->purchData as $pd) {
            $om->persist($pd);
        }
    }
}

