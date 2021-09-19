<?php

namespace Rialto\Stock\Item;

use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Item\Orm\StockItemRepository;

/**
 * Generates the next available stock code when given a pattern to match.
 *
 * The pattern should consist of:
 * 1) one or more letters, then
 * 2) zero or more digits, then either
 * 3a) one "*" character, or
 * 3b) one or more "#" characters.
 *
 * The "*" is a wildcard that means zero or more digits; the "#" is a wildcard
 * that means exactly one digit. If both wildcards are present, the "*" is
 * ignored.
 *
 * This allows the client to optionally specify any starting digits and the
 * total number of digits.
 */
class StockCodeGenerator
{
    /** @var StockItemRepository */
    private $repo;

    public function __construct(DbManager $dbm)
    {
        $this->repo = $dbm->getRepository(StockItem::class);
    }

    /**
     * @param string $pattern
     * @return boolean
     *  True if the given string is a valid stock code pattern.
     */
    public function isValid($pattern)
    {
        $pattern = strtoupper(trim($pattern));
        return preg_match(StockCodePattern::VALID, $pattern);
    }

    /**
     * @param string $pattern
     *  The pattern to match when generating a stock code.
     * @return string
     * @throws \OutOfBoundsException
     *  If there is no available stock code to match.
     */
    public function generateNext($pattern)
    {
        $pattern = new StockCodePattern($pattern);

        $matchingCodes = $this->repo->findMatchingStockCodes($pattern->getRegex());
//        logDebug($matchingCodes, "matching codes for $pattern");
        $digitArray = $this->findUsedDigits($pattern, $matchingCodes);
//        logDebug($digitArray, "digitArray for $pattern");
        $number = $pattern->findFirstAvailableNumber($digitArray);
        if ( null === $number ) throw new \OutOfBoundsException(
            "No available stock code matches the constraints"
        );
        return $pattern->createOption($number);
    }

    private function findUsedDigits(StockCodePattern $pattern, array $matchingCodes)
    {
        $digitArray = [];
        foreach ( $matchingCodes as $code ) {
            $matches = [];
            if ( preg_match($pattern->getRegex(), $code, $matches)) {
                $digits = $matches[2];
                $digitArray[] = $digits;
            }
        }
        return $digitArray;
    }
}

