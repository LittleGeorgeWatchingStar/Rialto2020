<?php

namespace Rialto\Stock\Count;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\IllegalStateException;
use Rialto\Stock\Bin\StockAdjustmentEvent;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Creates the stock moves and GL entries that are are required when
 * adjusting stock levels.
 */
class StockAdjustment
{
    /**
     * @var StockBin[]
     * @Assert\Valid(traverse=true)
     */
    private $bins = [];

    /** @var \DateTime */
    private $date;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(
     *   max = 150,
     *   maxMessage = "The memo cannot be longer than {{ limit }} characters.")
     */
    private $memo = '';

    /** @var \SplObjectStorage */
    private $totalsByItem;

    /** @var AdjustmentAccounting */
    private $accountingStrategy;

    /** @var EventDispatcherInterface */
    private $eventDispatcher = null;

    public function __construct($memo = '', AdjustmentAccounting $strategy = null)
    {
        $this->date = new \DateTime();
        $this->accountingStrategy = $strategy ?: new ByItemAccounting();
        $this->setMemo($memo);
    }

    /**
     * Call this method to override the default stock adjustment account;
     * for example, when throwing away defective stock covered under
     * warranty.
     */
    public function setAdjustmentAccount(GLAccount $account)
    {
        $this->accountingStrategy->setAdjustmentAccount($account);
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    public function getMemo()
    {
        return $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = trim($memo);
        $this->accountingStrategy->setMemo($this->memo);
    }

    public function addBin(StockBin $bin)
    {
        $key = $bin->getId() ?: 'new';
        $this->bins[$key] = $bin;
    }

    /** @return StockBin[] */
    public function getBins()
    {
        return $this->bins;
    }

    /**
     * @Assert\Callback
     */
    public function validateBins(ExecutionContextInterface $context)
    {
        foreach ($this->bins as $bin) {
            $error = $this->validateChangeAgainstAllocations($bin);
            if ($error) {
                $context->addViolation($error);
            }

            $error = $this->validateStandardCost($bin);
            if ($error) {
                $context->addViolation($error);
            }
        }
    }

    /**
     * @return string|null
     *  An error message; null if there is no error.
     */
    private function validateChangeAgainstAllocations(StockBin $bin)
    {
        /* If we can dispatch a stock bin change event, then the
         * allocation listener can adjust any allocations to match
         * the new quantity on the bin. If not, we need to prevent
         * the change. */
        if ($this->eventDispatcher) {
            return null;
        }
        $totalAllocated = $this->calculateTotalQtyAllocated($bin);
        if ($bin->getNewQty() < $totalAllocated) {
            return sprintf('The new quantity (%s) is lower than the total ' .
                'quantity allocated (%s).',
                number_format($bin->getNewQty()),
                number_format($totalAllocated)
            );
        }
        return null;
    }

    private function calculateTotalQtyAllocated(StockBin $bin)
    {
        $allocations = $bin->getAllocations();
        $totalQtyAllocated = 0;
        foreach ($allocations as $alloc) {
            $totalQtyAllocated += $alloc->getQtyAllocated();
        }
        return $totalQtyAllocated;
    }

    private function validateStandardCost(StockBin $bin)
    {
        $item = $bin->getStockItem();
        if ($item->getStandardCost() <= 0) {
            return "Standard cost of $item is not set.";
        }
        return null;
    }


    /**
     * Adjusts the stock levels and creates the accounting records that record
     * the change in stock value.
     *
     * @return Transaction|null
     */
    public function adjust(DbManager $dbm)
    {
        if (! $this->hasChanges()) {
            return null;
        }
        if (strlen($this->memo) == 0) {
            throw new IllegalStateException("memo has not been set");
        }

        $this->totalsByItem = new \SplObjectStorage();

        $sysType = SystemType::fetchStockAdjustment($dbm);
        $glTrans = new Transaction($sysType, $sysType->getNextNumber());
        $glTrans->setDate($this->date);
        $glTrans->setMemo($this->memo);
        $event = new StockAdjustmentEvent();
        foreach ($this->bins as $bin) {
            if (! $bin->hasNewQty()) {
                continue;
            }
            $this->accountingStrategy->addBin($bin);
            $bin->applyNewQty($glTrans);
            $event->addBin($bin);
        }

        $this->notify($event);
        $this->accountingStrategy->addEntries($glTrans);
        $dbm->persist($glTrans);
        return $glTrans;
    }

    public function hasChanges()
    {
        foreach ($this->bins as $bin) {
            if ($bin->hasNewQty()) {
                return true;
            }
        }
        return false;
    }

    private function notify(StockAdjustmentEvent $event)
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(StockEvents::STOCK_ADJUSTMENT, $event);
        }
    }
}
