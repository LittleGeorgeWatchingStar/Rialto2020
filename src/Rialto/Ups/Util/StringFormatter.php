<?php

namespace Rialto\Ups\Util;

class StringFormatter
{
    /**
     * Modifies the given string so that the UPS systems can handle it.
     *
     * @param string $string
     *        The string to be prepped.
     * @param int $max_length
     *        The maximum length that the string is allowed to be.
     * @return string
     *         The cleaned-up, possibly truncated string.
     */
    public function prepString($string, $max_length)
    {
        // Convert non-ASCII characters
        $string = utf8ToAscii($string);
        // Turn newlines into spaces
        $string = preg_replace('/[\n\r]/', ' ', $string);
        // HTML-escape all special chars
        $string = htmlentities($string);
        // Ensure string is within the length limits
        $string = substr($string, 0, $max_length);
        return $string;
    }
}
