<?php

namespace Rialto\Accounting\Bank\Transaction;


/**
 * Converts numbers like 123 into their textual representation:
 * "one hundred twenty three".
 *
 * This is used for printing cheques.
 */
class NumberToTextConverter
{
    private $magnitudes = [
        '',
        ' thousand',
        ' million',
        ' billion',
    ];

    private $teens = [
        ' ten',
        ' eleven',
        ' twelve',
        ' thirteen',
        ' fourteen',
        ' fifteen',
        ' sixteen',
        ' seventeen',
        ' eighteen',
        ' nineteen',
    ];

    private $ones = [
        '',
        ' one',
        ' two',
        ' three',
        ' four',
        ' five',
        ' six',
        ' seven',
        ' eight',
        ' nine',
    ];

    private $tens = [
        '',
        ' ten',
        ' twenty',
        ' thirty',
        ' forty',
        ' fifty',
        ' sixty',
        ' seventy',
        ' eighty',
        ' ninety',
    ];

    public function convertMoney($amount)
    {
        /* We have to deal very carefully with converting floats to ints
        /* to avoid rounding errors. */
        $amount = round($amount, 2);
        $dollars = floor($amount);
        $cents = round($amount - $dollars, 2) * 100;
        return sprintf('%s dollar%s and %s cent%s',
            $this->convertInt($dollars),
            $dollars == 1 ? '' : 's',
            $cents ? $this->convertInt($cents) : 'no',
            $cents == 1 ? '' : 's');
    }

    public function convertInt($number)
    {
        /* We have to deal very carefully with converting floats to ints
        /* to avoid rounding errors. */
        $int = (int) round($number);
        if ($int == 0) {
            return 'zero';
        }

        $text = '';
        $rest = $int;
        $magnitude = 0;
        while ($rest > 0) {
            $next = $rest % 1000;
            $text = $this->triplet($next) . $this->magnitudes[$magnitude] . $text;
            $magnitude++;
            $rest = floor($rest / 1000);
        }
        return trim($text);
    }

    private function triplet($triplet)
    {
        $ones = $triplet % 10;
        $tens = floor($triplet / 10) % 10;
        $hundreds = floor($triplet / 100) % 10;
        if ($tens == 1) {
            $text = $this->teens[$ones];
        } else {
            $text = $this->tens[$tens] . $this->ones[$ones];
        }
        if ($hundreds) {
            $text = $this->ones[$hundreds] . ' hundred' . $text;
        }
        return $text;
    }
}
