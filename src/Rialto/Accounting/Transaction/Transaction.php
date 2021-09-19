<?php

namespace Rialto\Accounting\Transaction;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Period\Period;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Location;
use Rialto\Stock\Move\StockMove;


/**
 * A general ledger (GL) transaction is two or more balanced GL entries
 * that record a single accounting event.
 *
 * @see GLEntry
 */
class Transaction implements AccountingEvent, RialtoEntity
{
    private $id;

    /** @var SystemType */
    private $systemType;

    /** @var int */
    private $groupNo;

    /** @var DateTime */
    private $date;

    /** @var Period */
    private $period;

    /** @var string */
    private $memo = '';

    /** @var GLEntry[] */
    private $entries;

    /** @var StockMove[] */
    private $stockMoves;

    /** @var int */
    private $chequeNo = 0;

    public static function fromEvent(AccountingEvent $event): self
    {
        assertion(null != $event->getSystemTypeNumber());
        $trans = new self($event->getSystemType(), $event->getSystemTypeNumber());
        $trans->setDate($event->getDate());
        $trans->setMemo($event->getMemo());
        return $trans;
    }

    /**
     * Factory method.
     */
    public static function fromInitiator(TransactionInitiator $initiator,
                                         ObjectManager $om,
                                         DateTime $date = null): self
    {
        $sysType = SystemType::fetch($initiator->getSystemTypeId(), $om);
        $groupNo = $initiator->getGroupNo();
        assertion(null != $groupNo);
        $trans = new self($sysType, $groupNo);
        $trans->setMemo($initiator->getMemo());
        $trans->setDate($date ?: new DateTime());
        return $trans;
    }

    /**
     * @param SystemType $sysType The type of transaction
     * @param int $groupNo (optional) The transaction group number
     */
    public function __construct(SystemType $sysType, $groupNo = null)
    {
        $this->systemType = $sysType;
        $this->groupNo = $groupNo ?: $sysType->getNextNumber();
        $this->setDate(new DateTime());
        $this->memo = $sysType->getName();
        $this->entries = new ArrayCollection();
        $this->stockMoves = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDate(): DateTime
    {
        return clone $this->date;
    }

    public function setDate(DateTime $date)
    {
        $this->date = clone $date;
        $this->period = Period::fetchForDate($date);
    }

    public function getMemo(): string
    {
        return $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = trim($memo);
    }

    public function getPeriod(): Period
    {
        return $this->period;
    }

    public function getSystemType()
    {
        return $this->systemType;
    }

    public function getSystemTypeNumber()
    {
        return $this->getGroupNo();
    }

    public function getGroupNo()
    {
        return $this->groupNo;
    }

    public function setChequeNumber($chequeNo)
    {
        $this->chequeNo = $chequeNo;
    }

    /**
     * Adds a GL entry to this transaction.
     */
    public function addEntry(GLAccount $account,
                             float $amount,
                             string $memo = null): GLEntry
    {
        $entry = new GLEntry(
            $this,
            $account,
            $amount,
            $memo ?: $this->memo
        );
        $entry->setChequeNumber($this->chequeNo);
        $this->entries[] = $entry;
        return $entry;
    }

    /** @return GLEntry[] */
    public function getEntries(): array
    {
        return $this->entries->getValues();
    }

    public function getMonetaryValue(): float
    {
        return array_sum($this->entries->map(function (GLEntry $e) {
                return abs($e->getAmount());
            })->getValues()) / 2;
    }

    /**
     * We really want to prevent invalid transactions from getting saved, so
     * this method is registered as a Doctrine lifecycle event handler before
     * inserting and updating.
     */
    public function validate()
    {
        $error = $this->getValidatorError();
        if ($error) {
            throw new IllegalStateException($error);
        }
    }

    private function getValidatorError()
    {
        if (!$this->systemType instanceof SystemType) {
            return 'Invalid system type';
        }
        if (!$this->groupNo) {
            return 'No group number set';
        }
        if (!$this->isBalanced()) {
            return 'Value of all entries does not sum to zero.';
        }
        return null; /* no errors */
    }

    /**
     * The sum of all entries in a transaction should always be zero in
     * double-entry bookkeeping.
     */
    public function isBalanced(): bool
    {
        $totalVal = 0;
        foreach ($this->entries as $entry) {
            $totalVal += $entry->getAmount();
        }
        static $SCALE = 10;
        return bceq($totalVal, 0, $SCALE);
    }

    public function addStockMove(StockMove $move)
    {
        $move->setTransaction($this);
        $this->stockMoves[] = $move;
    }

    /** @return StockMove[] */
    public function getStockMoves(): array
    {
        return $this->stockMoves->getValues();
    }

    public function getMoveTotal(): float
    {
        return array_sum($this->stockMoves->map(function (StockMove $m) {
            return $m->getQuantity();
        })->getValues());
    }

    /**
     * Transfers a bin to the given location and attaches the corresponding
     * stock moves to this transfer.
     */
    public function moveBin(StockBin $bin, Location $destination)
    {
        if ($bin->isAtLocation($destination)) {
            throw new IllegalStateException("$bin is already at $destination");
        }
        $qty = $bin->getQuantity();
        $from = StockMove::fromBin($bin);
        $from->setQuantity(-$qty);
        $this->addStockMove($from);

        $bin->setLocation($destination);
        $to = StockMove::fromBin($bin);
        $to->setQuantity($qty);
        $this->addStockMove($to);
    }
}
