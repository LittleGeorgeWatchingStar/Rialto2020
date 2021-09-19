<?php

namespace Rialto\Stock\Category;

use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Stock\Cost\StandardCostException;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Level\StockLevelService;

/**
 * Changes the category of an existing stock item and does any necessary
 * stock accounting.
 */
class CategoryChange
{
    /** @var StockLevelService */
    private $stockLevel;

    public function __construct(StockLevelService $levels)
    {
        $this->stockLevel = $levels;
    }

    /**
     * @return Transaction|null
     *  Returns null if no accounting transaction needs to be done.
     */
    public function changeCategory(StockItem $item, StockCategory $newCategory)
    {
        $oldCategory = $item->getCategory();
        $item->setCategory($newCategory);

        $oldAccount = $oldCategory->getStockAccount();
        $newAccount = $newCategory->getStockAccount();

        /* Do we need to do any accounting at all? */
        if ($oldAccount->equals($newAccount)) {
            return null;
        }
        $qtyOnHand = $this->stockLevel->getTotalQtyInStock($item);
        if ($qtyOnHand <= 0) return null;

        $stdCost = $item->getStandardCost();
        if ($stdCost <= 0) {
            throw new StandardCostException($item, $stdCost);
        }
        $valueDiff = $stdCost * $qtyOnHand;

        $sysType = SystemType::fetchStockAdjustment();
        $transaction = new Transaction($sysType);
        $transaction->setMemo(sprintf('Category of %s changed from %s to %s',
            $item->getSku(),
            $oldCategory->getName(),
            $newCategory->getName()));

        $transaction->addEntry($oldAccount, -$valueDiff);
        $transaction->addEntry($newAccount, $valueDiff);
        return $transaction;
    }

}
