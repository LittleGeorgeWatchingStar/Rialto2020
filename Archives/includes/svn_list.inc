<?php


/**
 * @deprecated  Use CsvParser#parseString() instead.
 */
function csv_parse($str,$f_delim = ',',$r_delim = "\n",$qual = '"') {
    require_once 'gumstix/filetypes/CsvParser.php';
    return CsvParser::create($str, $f_delim, $qual, '\\', $r_delim)->getRows();
}


