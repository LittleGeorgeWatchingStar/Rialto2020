<?php

namespace Rialto\Stock\Item;

/**
 * Helper class for StockCodeGenerator.
 *
 * @see StockCodeGenerator
 */
class StockCodePattern
{
    const DEFAULT_NUM_DIGITS = 3;
    const VALID = '/^([A-Z]+)([0-9]*)([#*]+)$/';

    public $letters;
    private $fixedDigits;
    private $wildcards;

    public function __construct($pattern)
    {
        $pattern = strtoupper(trim($pattern));
        $matches = [];
        if (! preg_match(self::VALID, $pattern, $matches) ) {
            throw new \InvalidArgumentException("Pattern must match ". self::VALID);
        }

        $this->letters = $matches[1];
        $this->fixedDigits = $matches[2];
        $this->wildcards = $matches[3];

        /* Only allow one type of wildcard: "#" overrides "*". */
        if ( false !== strpos($this->wildcards, '#') ) {
            $this->wildcards = str_replace('*', '', $this->wildcards);
        }
    }

    /** @return string */
    public function __toString()
    {
        return $this->letters . $this->fixedDigits . $this->wildcards;
    }

    /** @return string */
    public function getRegex()
    {
        $wildcards = str_replace('*', '[0-9]*', $this->wildcards);
        $wildcards = str_replace('#', '[0-9]', $wildcards);
        return "/^({$this->letters})({$this->fixedDigits}{$wildcards})$/";
    }

    /** @return int */
    public function getStartingNumber()
    {
        return (int) str_pad($this->fixedDigits, $this->getPadLength(), '0');
    }

    private function getMaxNumberAllowed()
    {
        return (int) str_pad($this->fixedDigits, $this->getPadLength(), '9');
    }

    private function getPadLength()
    {
        if ( $this->wildcards == '*') {
            return self::DEFAULT_NUM_DIGITS;
        }
        else {
            return mb_strlen($this->fixedDigits . $this->wildcards);
        }
    }

    /** @return int|null */
    public function findFirstAvailableNumber(array $digitArray)
    {
        $startAt = $this->getStartingNumber();
        $maxNumber = $this->getMaxNumberAllowed();
        $newNumber = $startAt;
        foreach ( $digitArray as $digits ) {
//            logDebug("startAt $startAt; max $maxNumber; digits $digits; new $newNumber");
            if ( (int) $digits == $newNumber ) $newNumber++;
            if ( $newNumber > $maxNumber ) return null;
        }
        return $newNumber;
    }

    /** @return string */
    public function createOption($number)
    {
        return $this->letters . str_pad($number, $this->getPadLength(), '0', STR_PAD_LEFT);
    }
}
