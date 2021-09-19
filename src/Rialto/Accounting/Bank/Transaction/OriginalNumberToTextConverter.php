<?php

namespace Rialto\Accounting\Bank\Transaction;


/**
 * @deprecated
 *
 * I challenge all ye who enter here to understand how this works!
 *
 * Also, this does not pass the test suite.
 */
class OriginalNumberToTextConverter
{
    public function convertMoney($num)
    {
        // eg. original number = 198.25
        //make num a string, and reverse it, because we run through it backwards
        $num = strrev(round($num, 2)); // "52.891"
        $str = '';
        $skip = 0;
        $start = 0;
        $aa = [];
        for ($i = -strlen($num); $i < 0; $i++) {
            //substitute for any 10-19's that should come out
            if (substr($num, $i, 1) == 1 && (strlen($num) + $i) % 3 == 1) {
                switch (substr($num, $i - 1, 1)) { //get second digit
                    case '0':
                        $aa[] = 'ten ';
                        break;
                    case '1':
                        $aa[] = 'eleven ';
                        break;
                    case '2':
                        $aa[] = 'twelve ';
                        break;
                    case '3':
                        $aa[] = 'thirteen ';
                        break;
                    case '4':
                        $aa[] = 'fourteen ';
                        break;
                    case '5':
                        $aa[] = 'fifteen ';
                        break;
                    case '6':
                        $aa[] = 'sixteen ';
                        break;
                    case '7':
                        $aa[] = 'seventeen ';
                        break;
                    case '8':
                        $aa[] = 'eighteen ';
                        break;
                    case '9':
                        $aa[] = 'nineteen ';
                        break;
                };
                $num = substr_replace($num, 'xx', $i - 1, 2);
                $skip = 1;
                $start = -1; //need to start one earlier to compensate
            }
        }
        for ($i = -strlen($num) + $start; $i < 0; $i++) {
            $astr = '';
            if (! $skip) {
                switch (substr($num, $i, 1)) {
                    case '1':
                        if ((strlen($num) + $i) % 3 == 1) {
                            //dunno
                        } else {
                            $astr = 'one ';
                        }
                        break;
                    case '2':
                        if ((strlen($num) + $i) % 3 == 1) {
                            $astr = 'twenty ';
                        } else {
                            $astr = 'two ';
                        }
                        break;
                    case '3':
                        if ((strlen($num) + $i) % 3 == 1) {
                            $astr = 'thirty ';
                        } else {
                            $astr = 'three ';
                        }
                        break;
                    case '4':
                        if ((strlen($num) + $i) % 3 == 1) {
                            $astr = 'forty ';
                        } else {
                            $astr = 'four ';
                        }
                        break;
                    case '5':
                        if ((strlen($num) + $i) % 3 == 1) {
                            $astr = 'fifty ';
                        } else {
                            $astr = 'five ';
                        }
                        break;
                    case '6':
                        if ((strlen($num) + $i) % 3 == 1) {
                            $astr = 'sixty ';
                        } else {
                            $astr = 'six ';
                        }
                        break;
                    case '7':
                        if ((strlen($num) + $i) % 3 == 1) {
                            $astr = 'seventy ';
                        } else {
                            $astr = 'seven ';
                        }
                        break;
                    case '8':
                        if ((strlen($num) + $i) % 3 == 1) {
                            $astr = 'eighty ';
                        } else {
                            $astr = 'eight ';
                        }
                        break;
                    case '9':
                        if ((strlen($num) + $i) % 3 == 1) {
                            $astr = 'ninety ';
                        } else {
                            $astr = 'nine ';
                        }
                        break;
                    case '0':
                        if ((strlen($num) + $i) == 1 && substr($num, $i - 1, 2) == '00') {
                            //don't display 0, except if cents=0
                            $astr = 'no ';
                            $skip = 1;
                        }
                        break;
                    case 'x':
                        $astr = current($aa);
                        next($aa);
                        $skip = 1;
                        break;
                }
            } else {
                $skip--;
            }
            $str = $astr . $this->n2tmod(strlen($num) + $i + 1) . $str;
        }
        if (substr($str, 0, 3) == "dol") { //check for zero dollars
            $str = "Zero " . $str;
        }
        $str = str_replace('thousand hundred', 'thousand', $str);
        return $str;
    }

    private static function n2tmod($pos)
    {
        switch ($pos) {
            case '1':
                return 'cents ';
            case '3':
                return 'dollars and ';
            case '6':
                return 'hundred ';
            case '7':
                return 'thousand ';
            case '9':
                return 'hundred ';
            case '10':
                return 'million ';
            case '12':
                return 'hundred ';
            default:
                return '';
        }
    }
}
