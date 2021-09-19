<?php

namespace Rialto\Util\Strings;

/**
 * Class for various handy operations on strings and text.
 */
class TextFormatter
{
    /**
     * Takes a string in CamelCase (eg, "SomethingLikeThis") and returns it as
     * a string of separate words ("something like this").
     *
     * @param string $string
     * @return string
     */
    public function camelToWords($string)
    {
        // "blahBlah" => "blah blah"
        $string = preg_replace_callback('/([A-Z][a-z])/', function(array $matches) {
            return ' '. strtolower($matches[1]);
        }, $string);
        // "thingyID" => "thingy ID"
        $string = preg_replace('/([a-z])([A-Z])/', '$1 $2', $string);
        return trim($string);
    }

    public function getterToWords($string)
    {
        $string = preg_replace('/^get/', '', $string);
        return $this->camelToWords($string);
    }

    public function stripControlCharacters($string)
    {
        return preg_replace('/[^(\x20-\x7F)]*/', '', $string);
    }

    public function htmlToText($html)
    {
        $converted = $this->convertNewlines($html);
        return trim(strip_tags($converted));
    }

    private function convertNewlines($html)
    {
        static $replace = [
            "<br>",
            "<br/>",
            "<br />",
            "<p>",
            "</p>",
            "<div>",
        ];

        $converted = $html;
        foreach ( $replace as $old ) {
            $converted = str_replace($old, PHP_EOL, $converted);
        }
        return $converted;
    }
}
