<?php

namespace Rialto\Accounting\Bank\Statement\Parser;


use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Gumstix\Filetype\CsvFile;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Statement\Orm\BankStatementRepository;
use Rialto\Accounting\Ledger\Account\GLAccount;

/**
 * Parses BankStatement records from a Silicon Valley Bank CSV file.
 */
final class SiliconValleyBankStatementParser implements BankStatementParser
{
    /**
     * Sometimes the bank ref or customer ref is not populated by the
     * bank until a few days later. If both fields are missing and the
     * transaction is very recent, we'll ignore it and try again later
     * in the hopes that the field will be populated. However, if the
     * transaction is old enough, we'll just add it anyway.
     */
    const REFERENCE_DELAY = 3; // days

    /** @var ObjectManager */
    private $om;

    /** @var BankStatementRepository */
    private $repo;

    /** @var BankAccount */
    private $bankAccount;

    private $numSkipped;
    private $numAdded;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository(BankStatement::class);
        $this->bankAccount = $om->getRepository(BankAccount::class)
            ->find(GLAccount::REGULAR_CHECKING_ACCOUNT);
    }

    /** @return ParseResult[] */
    public function parse(CsvFile $file): array
    {
        $this->numSkipped = 0;
        $this->numAdded = 0;
        $results = [];
        foreach ($file as $i => $row) {
            $result = new ParseResult($row);
            $results[] = $result;
            if (count($row) < 6) {
                $result->setReason("Not enough columns");
                continue;
            }
            $date = DateTime::createFromFormat('m/d/Y', $row[0]);
            if (! $date) {
                $result->setReason(sprintf("Invalid date '%s'", $row[0]));
                continue;
            }
            $amount = $this->parseAmountFromRow($row);
            $bankRef = $row[3];
            $custRef = $row[4];
            if (! $this->referenceFieldsAreValid($date, $bankRef, $custRef)) {
                $result->setReason("No bank ref or customer ref");
                continue;
            }
            $text = $row[5];
            $statement = $this->repo->findOrCreate($this->bankAccount,
                $date, $amount, $bankRef, $custRef, $text);
            $this->om->persist($statement);
            $result->setStatement($statement);
            if ($result->isSkipped()) {
                $this->numSkipped++;
            } else {
                $this->numAdded++;
            }
        }
        return $results;
    }

    private function parseAmountFromRow(array $row): float
    {
        return $this->parseAmount($row[1], $row[2]);
    }

    private function parseAmount($description, $amountText)
    {
        $amount = (float) str_replace(',', '', $amountText);
        if (! $this->isCredit($description)) {
            $amount = -$amount;
        }
        return $amount;
    }

    private function referenceFieldsAreValid(DateTime $date, $bankRef, $custRef)
    {
        if ($bankRef || $custRef) {
            return true;
        }
        $today = new DateTime();
        if ($date > $today) {
            return false;
        }
        $diff = $today->diff($date, true);
        return $diff->days >= self::REFERENCE_DELAY;
    }

    private function isCredit($description)
    {
        return preg_match('/CREDIT|DEPOSIT/', $description);
    }

    public function getNumAdded(): int
    {
        return $this->numAdded;
    }

    public function getNumSkipped(): int
    {
        return $this->numSkipped;
    }
}
