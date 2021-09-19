<?php

namespace Rialto\Stock\Category;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A category to which a stock item can belong.
 *
 * @see StockItem
 */
class StockCategory implements RialtoEntity
{
    const PART = 1;
    const PRODUCT = 2;
    const PCB = 3;
    const ENCLOSURE = 4;
    const SHIPPING = 6;
    const BOARD = 7;
    const MODULE = 9;
    const SOFTWARE = 10;
    const BAG = 'BAG';

    const FINISHED_GOODS = 'F';
    const RAW_MATERIALS = 'M';
    const DUMMY_ITEMS_NO_MOVEMENTS = 'D';
    const LABOUR = 'L';

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=6)
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=20)
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=1)
     */
    private $stockType;

    /**
     * @var GLAccount
     * @Assert\NotNull
     */
    private $stockAccount;

    /**
     * @var GLAccount
     * @Assert\NotNull
     */
    private $adjustmentAccount;

    /**
     * @var GLAccount
     * @Assert\NotNull
     */
    private $purchasePriceVarianceAccount;

    /**
     * @var GLAccount
     * @Assert\NotNull
     */
    private $materialUsageVarianceAccount;

    /**
     * @var GLAccount
     * @Assert\NotNull
     */
    private $wipAccount;

    public function __construct($id)
    {
        $this->id = strtoupper(trim($id));
    }

    /**
     * Fetches the StockCategory object whose CategoryID is given.
     *
     * @param string $id The ID of the category to fetch.
     * @return StockCategory
     */
    private static function fetch($id, ObjectManager $om)
    {
        /** @var StockCategory|null $result */
        $result = $om->find(self::class, $id);
        assertion(null !== $result);
        return $result;
    }

    /** @return StockCategory */
    public static function fetchPCB(ObjectManager $om)
    {
        return self::fetch(self::PCB, $om);
    }

    /** @return StockCategory */
    public static function fetchBoard(ObjectManager $om)
    {
        return self::fetch(self::BOARD, $om);
    }

    /** @return StockCategory */
    public static function fetchProduct(ObjectManager $om)
    {
        return self::fetch(self::PRODUCT, $om);
    }

    /** @return StockCategory */
    public static function fetchPart(ObjectManager $om)
    {
        return self::fetch(self::PART, $om);
    }

    public static function fetchSoftware(ObjectManager $om): self
    {
        return self::fetch(self::SOFTWARE, $om);
    }

    /**
     * @return string[]
     */
    public static function getSellableIds(): array
    {
        return [
            StockCategory::BOARD,
            StockCategory::PRODUCT,
        ];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function equals(StockCategory $other): bool
    {
        return $other->getId() == $this->id;
    }

    /**
     * @return GLAccount
     */
    public function getAdjustmentAccount()
    {
        return $this->adjustmentAccount;
    }

    public function setAdjustmentAccount(GLAccount $account)
    {
        $this->adjustmentAccount = $account;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @deprecated use getName() instead
     */
    public function getDescription()
    {
        return $this->getName();
    }

    /**
     * @return GLAccount
     */
    public function getPurchasePriceVarianceAccount()
    {
        return $this->purchasePriceVarianceAccount;
    }

    public function setPurchasePriceVarianceAccount(GLAccount $account)
    {
        $this->purchasePriceVarianceAccount = $account;
    }

    /**
     * @return GLAccount
     */
    public function getStockAccount()
    {
        return $this->stockAccount;
    }

    public function setStockAccount(GLAccount $account)
    {
        $this->stockAccount = $account;
    }

    /**
     * @return string
     */
    public function getStockAccountId()
    {
        return $this->stockAccount->getId();
    }

    public function getStockType()
    {
        return $this->stockType;
    }

    public function setStockType($stockType)
    {
        $this->stockType = trim($stockType);
    }

    public function getMaterialUsageVarianceAccount()
    {
        return $this->materialUsageVarianceAccount;
    }

    public function setMaterialUsageVarianceAccount(GLAccount $account)
    {
        $this->materialUsageVarianceAccount = $account;
    }

    public function getWipAccount()
    {
        return $this->wipAccount;
    }

    public function setWipAccount(GLAccount $account)
    {
        $this->wipAccount = $account;
    }

    /**
     * @param StockCategory|int $category The category or category ID
     * @return bool
     */
    public function isCategory($category): bool
    {
        if ($category instanceof StockCategory) {
            $category = $category->getId();
        }
        return $this->id == $category;
    }

    public function isProduct(): bool
    {
        return self::PRODUCT == $this->id;
    }

    public function isEnclosure(): bool
    {
        return self::ENCLOSURE == $this->id;
    }

    public function isShipping(): bool
    {
        return self::SHIPPING == $this->id;
    }

    public function isPart(): bool
    {
        return self::PART == $this->id;
    }

    public function isPCB(): bool
    {
        return self::PCB == $this->id;
    }

    public function isSoftware(): bool
    {
        return self::SOFTWARE == $this->id;
    }

    public function isSellable(): bool
    {
        return in_array($this->id, self::getSellableIds());
    }

    /**
     * True if items in this category are automatically ESD-sensitive.
     *
     * Other items might be, too, for other reasons.
     */
    public function isEsdSensitive(): bool
    {
        return in_array($this->id, [
            self::PART,
            self::PCB,
            self::BOARD,
            self::MODULE,
        ]);
    }
}

