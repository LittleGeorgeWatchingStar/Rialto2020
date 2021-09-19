<?php

namespace Rialto\Accounting\Supplier;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class SupplierCreditNote
{
    /** @var Supplier */
    private $supplier;

    /**
     * @var SupplierCreditItem[]
     * @Assert\Count(min=1)
     */
    private $items;

    /**
     * @var GLAccount
     * @Assert\NotNull
     */
    private $toAccount;

    /**
     * @var DateTime
     * @Assert\Date
     */
    private $date;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $reference;

    private $comments;

    public function __construct(Supplier $supplier, GLAccount $toAccount = null)
    {
        $this->supplier = $supplier;
        $this->items = new ArrayCollection();
        $this->toAccount = $toAccount;
        $this->date = new DateTime();
        $this->addItem(new SupplierCreditItem());
    }

    public function getItems()
    {
        return $this->items->toArray();
    }

    public function addItem(SupplierCreditItem $item)
    {
        $this->items[] = $item;
    }

    public function removeItem(SupplierCreditItem $item)
    {
        $this->items->removeElement($item);
    }

    public function getToAccount()
    {
        return $this->toAccount;
    }

    public function setToAccount(GLAccount $toAccount)
    {
        $this->toAccount = $toAccount;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = trim($reference);
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = trim($comments);
    }

    /** @return SupplierTransaction */
    public function createTransaction(DbManager $dbm)
    {
        $sysType = SystemType::fetchDebitNote($dbm);
        $glTrans = new Transaction($sysType);
        $glTrans->setDate($this->date);
        $total = 0;
        foreach ( $this->items as $item ) {
            $glTrans->addEntry(
                $item->getAccount(),
                -$item->getAmount(),
                $item->getMemo());
            $total += $item->getAmount();
        }
        $glTrans->addEntry($this->toAccount, $total, $this->getMemo($total));

        $dbm->persist($glTrans);

        $suppTrans = new SupplierTransaction($glTrans, $this->supplier);
        $suppTrans->setReference($this->reference);
        $suppTrans->calculateDueDate();
        $suppTrans->setSubtotalAmount(-$total);
        $suppTrans->setMemo($this->comments);

        $dbm->persist($suppTrans);

        return $suppTrans;
    }

    private function getMemo($totalAmount)
    {
        return sprintf('%s - Credit Note - %s %s%s @ a rate of %s',
            $this->supplier->getId(),
            $this->reference,
            Currency::USD,
            number_format($totalAmount, 2),
            1.000);
    }

}

