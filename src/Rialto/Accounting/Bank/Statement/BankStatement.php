<?php

namespace Rialto\Accounting\Bank\Statement;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Entity\RialtoEntity;

class BankStatement implements RialtoEntity
{
    const MONEY_PRECISION = 2;

    const PART_DELIMITER = ';';
    const PART_ACCOUNT = 0;
    const PART_ORG = 1;
    const PART_BNF = 1;
    const PART_OBI = 2;

    private $id;
    private $date;
    private $amount;
    private $description;
    private $bankReference;
    private $customerReference;
    private $bankText;

    /** @var BankAccount */
    private $bankAccount;

    /** @var BankStatementMatch[] */
    private $matches;

    public function __construct(
        BankAccount $bankAccount,
        DateTime $date,
        $amount,
        $bankRef,
        $custRef,
        $description)
    {
        $this->bankAccount = $bankAccount;
        $this->date = $date;
        $this->amount = $amount;
        $this->bankReference = (int) $bankRef;
        $this->customerReference = (int) $custRef;
        $this->description = substr(trim($description), 0, 255);
        $this->bankText = $description;  // TODO: is one of these unneeded?
        $this->matches = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function isNew()
    {
        return ! $this->id;
    }

    /** @return \DateTime */
    public function getDate()
    {
        return $this->date;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getAmountOutstanding()
    {
        return $this->amount - $this->getAmountCleared();
    }

    public function getAmountCleared()
    {
        $total = 0;
        foreach ( $this->getMatches() as $match ) {
            $total += $match->getAmountCleared();
        }
        return $total;
    }

    public function isDeposit()
    {
        return $this->amount > 0;
    }

    /** @return BankStatementMatch[] */
    public function getMatches()
    {
        return $this->matches->toArray();
    }

    private function round($amount)
    {
        return round($amount, self::MONEY_PRECISION);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function isWireReceipt()
    {
        return $this->isWireTransfer('WIRE IN');
    }

    private function isWireTransfer($wireTransType)
    {
        $account = $this->getDescriptionPart(self::PART_ACCOUNT);
        return strpos($account, $wireTransType) !== false;
    }

    private function getDescriptionPart($part)
    {
        $parts = explode(self::PART_DELIMITER, $this->description);
        return isset($parts[$part]) ? $parts[$part] : null;
    }

    /** @return string|null */
    public function getCustomerName()
    {
        if (! $this->isWireReceipt() ) {
            return $this->getDescription();
        }
        $orgPart = $this->getDescriptionPart(self::PART_ORG);
        if (! $orgPart ) { return null; }
        $matches = [];
        preg_match('/ORG (.*)/', $orgPart, $matches);
        if ( count($matches) >= 2 ) {
            return trim($matches[1]);
        }
        return null;
    }

    /** @return int[]|null */
    public function getPossibleOrderNumbers()
    {
        if (! $this->isWireReceipt() ) { return null; }
        $obiPart = $this->getDescriptionPart(self::PART_OBI);
        if (! $obiPart ) { return []; }
        $matches = [];
        preg_match_all('/\d+/', $obiPart, $matches);
        if ( count($matches) > 0 ) {
            return $matches[0];
        }
        return [];
    }

    /** @return string|null */
    public function getTransactionId()
    {
        if (! $this->isWireTransfer('WIRE') ) { return null; }
        $txnPart = $this->getDescriptionPart(self::PART_ACCOUNT);
        $matches = [];
        preg_match('/WIRE (IN|OUT) (.*)/', $txnPart, $matches);
        if ( count($matches) > 2 ) {
            return $matches[2];
        }
        return null;
    }

    public function getBankReference()
    {
        return $this->bankReference;
    }

    public function getCustomerReference()
    {
        return $this->customerReference;
    }

    public function getBankText()
    {
        return $this->bankText;
    }

    /** @return BankTransaction[] */
    public function getBankTransactions()
    {
        return array_map(function(BankStatementMatch $match) {
            return $match->getBankTransaction();
        }, $this->getMatches());
    }

    /** @return BankStatementMatch */
    public function addBankTransaction(BankTransaction $trans)
    {
        $amount = $this->calculateAmountToMatch($trans);

        $match = new BankStatementMatch($this, $trans);
        $match->setAmountCleared($amount);
        $this->matches[] = $match;
        $trans->addMatch($match);

        /* Maintain legacy fields */
        $trans->refreshAmountCleared();

        return $match;
    }

    private function calculateAmountToMatch(BankTransaction $trans)
    {
        $tAmt = $trans->getAmountOutstanding();
        $sAmt = $this->getAmountOutstanding();
        $absAmount = min( abs($tAmt), abs($sAmt) );
        if ( $absAmount == 0 ) {
            throw new \InvalidArgumentException(sprintf(
                'Amount to clear between BankStatement %s and BankTransaction '.
                '%s is zero',
                $this->getId(),
                $trans->getId()
            ));
        }

        $tSign = $this->getSign($tAmt);
        $sSign = $this->getSign($sAmt);
        if ( $tSign != $sSign ) {
            throw new \InvalidArgumentException(sprintf(
                'The signs of BankStatement %s and BankTransaction %s '.
                'do not match',
                $this->getId(),
                $trans->getId()
            ));
        }
        $amount = $this->round($tSign * $absAmount);

        return $amount;
    }

    private function getSign($amt)
    {
        return round($amt / abs($amt));
    }

    public function getBankAccount(): BankAccount
    {
        return $this->bankAccount;
    }
}
