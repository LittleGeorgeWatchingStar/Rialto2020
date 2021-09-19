<?php

function n2tmod($pos)
{
    switch ($pos) {
        case '1':
            return 'cents ';
            break;
        case '3':
            return 'dollars and ';
            break;
        case '6':
            return 'hundred ';
            break;
        case '7':
            return 'thousand ';
            break;
        case '9':
            return 'hundred ';
            break;
        case '10':
            return 'million ';
            break;
        case '12':
            return 'hundred ';
            break;
    }
}

function numtotext($num)
{
    $num = strval(strrev(number_format($num, 2, '.', ''))); //make num a string, and reverse it, because we run through it backwards
    $str = '';
    $skip = 0;
    $start = 0;
    $aa = array();
    for ($i = -strlen($num); $i < 0; $i++) { //substitute for any 10-19's that should come out
        $astr = '';
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
        };
    };
    for ($i = -strlen($num) + $start; $i < 0; $i++) {
        $astr = '';
        if (! $skip) {
            switch (substr($num, $i, 1)) {
                case '1':
                    if ((strlen($num) + $i) % 3 == 1) {
                        //dunno
                    } else {
                        $astr = 'one ';
                    };
                    break;
                case '2':
                    if ((strlen($num) + $i) % 3 == 1) {
                        $astr = 'twenty ';
                    } else {
                        $astr = 'two ';
                    };
                    break;
                case '3':
                    if ((strlen($num) + $i) % 3 == 1) {
                        $astr = 'thirty ';
                    } else {
                        $astr = 'three ';
                    };
                    break;
                case '4':
                    if ((strlen($num) + $i) % 3 == 1) {
                        $astr = 'forty ';
                    } else {
                        $astr = 'four ';
                    };
                    break;
                case '5':
                    if ((strlen($num) + $i) % 3 == 1) {
                        $astr = 'fifty ';
                    } else {
                        $astr = 'five ';
                    };
                    break;
                case '6':
                    if ((strlen($num) + $i) % 3 == 1) {
                        $astr = 'sixty ';
                    } else {
                        $astr = 'six ';
                    };
                    break;
                case '7':
                    if ((strlen($num) + $i) % 3 == 1) {
                        $astr = 'seventy ';
                    } else {
                        $astr = 'seven ';
                    };
                    break;
                case '8':
                    if ((strlen($num) + $i) % 3 == 1) {
                        $astr = 'eighty ';
                    } else {
                        $astr = 'eight ';
                    };
                    break;
                case '9':
                    if ((strlen($num) + $i) % 3 == 1) {
                        $astr = 'ninety ';
                    } else {
                        $astr = 'nine ';
                    };
                    break;
                case '0':
                    if ((strlen($num) + $i) == 1 && substr($num, $i - 1, 2) == '00') { //don't display 0, except if cents=0
                        $astr = 'no ';
                        $skip = 1;
                    };
                    break;
                case 'x':
                    $astr = current($aa);
                    next($aa);
                    $skip = 1;
                    break;
            };
        } else {
            $skip--;
        };
        $str = $astr . n2tmod(strlen($num) + $i + 1) . $str;
    };
    if (substr($str, 0, 3) == "dol") { //check for zero dollars
        $str = "Zero " . $str;
    };
    $str = str_replace('thousand hundred', 'thousand', $str);
    return $str;
}


/********************************************/
/** STANDARD MESSAGE HANDLING & FORMATTING **/
/********************************************/

function prnMsg($msg, $type = 'info', $prefix = '')
{

    echo '<P>' . getMsg($msg, $type, $prefix) . '</P>';

}//prnMsg

function getMsg($msg, $type = 'info', $prefix = '')
{
    $Colour = '';
    switch ($type) {
        case 'error':
            $Colour = 'red';
            $prefix = $prefix ? $prefix : _('ERROR') . ' ' . _('Message Report');
            break;
        case 'warn':
            $Colour = 'maroon';
            $prefix = $prefix ? $prefix : _('WARNING') . ' ' . _('Message Report');
            break;
        case 'success':
            $Colour = 'darkgreen';
            $prefix = $prefix ? $prefix : _('SUCCESS') . ' ' . _('Report');
            break;
        case 'info':
        default:
            $prefix = $prefix ? $prefix : _('INFORMATION') . ' ' . _('Message');
            $Colour = 'navy';
    }
    return '<FONT COLOR="' . $Colour . '"><B>' . $prefix . '</B> : ' . $msg . '</FONT>';
}//getMsg

?>
