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
 * Parses BankStatement records from an HSBC CSV file.
 */
final class HsbcStatementParser implements BankStatementParser
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
            ->find(GLAccount::WGK_PAYMENT_ACCOUNT);
    }

    /**
     * @return ParseResult[]
     */
    public function parse(CsvFile $file): array
    {
        $this->numSkipped = 0;
        $this->numAdded = 0;
        $results = [];
        foreach ($file as $i => $row) {
            $result = new ParseResult($row);
            $results[] = $result;
            if (count($row) < 28) {
                $result->setReason("Not enough columns");
                continue;
            }
            $date = DateTime::createFromFormat('Y/m/d', $row[27]);
            if (! $date) {
                $result->setReason(sprintf("Invalid date '%s'", $row[27]));
                continue;
            }
            $amount = $this->parseAmountFromRow($row);
            $bankRef = $row[17];
            $custRef = $row[19];
            if (! $this->referenceFieldsAreValid($date, $bankRef, $custRef)) {
                $result->setReason("No bank ref or customer ref");
                continue;
            }
            $text = $row[18];
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
        $credit = (float) trim(str_replace(',', '', $row[23]));
        $debit = (float) trim(str_replace(',', '', $row[24]));

        if ($credit && $debit) {
            throw new \InvalidArgumentException("Expected to find either credit or debit, found both.");
        }

        if ($credit) {
            return $credit;
        }

        if ($debit) {
            return $debit;
        }

        throw new \InvalidArgumentException("Expected to find either credit or debit, found neither.");
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

    public function getNumAdded(): int
    {
        return $this->numAdded;
    }

    public function getNumSkipped(): int
    {
        return $this->numSkipped;
    }
}
