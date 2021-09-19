<?php

namespace Rialto\Purchasing\Invoice\Parser;

use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\ValidSku;


class Matcher
{
    private $dbm;
    private $type;
    private $pattern;
    private $regex;

    public function __construct(Field $field, DbManager $dbm)
    {
        $this->dbm = $dbm;
        $this->type = $field->type;
        $this->pattern = $field->pattern;
        if (!($this->type || $this->pattern)) {
            throw new SupplierInvoiceParserException(sprintf(
                'Field %s is invalid', $field->name
            ));
        }

        switch ($this->type) {
            case 'date':
                $this->regex = $this->getRegexForDate($this->pattern);
                break;
            case 'digits':
                $this->regex = '/\b(\d+)\b/';
                break;
            case 'dollar':
                $this->regex = sprintf('/\$\s*(%s)/', $this->getRegexForFloat());
                break;
            case 'float':
                $this->regex = sprintf('/(%s)/', $this->getRegexForFloat());
                break;
            case 'int':
                $this->regex = sprintf('/(%s)/', $this->getRegexForInt());
                break;
            case 'stockId':
                $this->regex = sprintf('/(%s)/i', ValidSku::REGEX_BARE);
                break;
            case 'text':
                $this->regex = '/(.*)/';
                break;
            case 'ups':
                $this->regex = '/\b(\d[a-zA-Z]\w+)\b/';
                break;
            default:
                $this->regex = sprintf('/\b(%s)\b/', $this->pattern);
        }
    }

    public function getMatch($potential)
    {
        $matches = [];
//        logDebug("Does $potential match {$this->regex}?");
        if (preg_match($this->regex, $potential, $matches)) {
            assert(count($matches) > 1);
//            logDebug(" Yes");
            return $this->prepText($matches[1]);
        }
        return null;
    }

    private function prepText($text)
    {
        $text = $this->prepNumeric($text);
        switch ($this->type) {
            case 'date':
                return date('Y-m-d', strtotime($text));
            case 'dollar':
            case 'float':
                return (float) $this->prepNumeric($text);
            case 'int':
                return (int) $this->prepNumeric($text);
            case 'stockId':
                /** @var StockItemRepository $repo */
                $repo = $this->dbm->getRepository(StockItem::class);
                $text = trim($text);
                if ($repo->isExistingStockId($text)) {
                    return $text;
                } else {
                    return null;
                }
            default:
                return $text;
        }
    }

    /**
     * @param string $val
     * @return string
     */
    private function prepNumeric($val)
    {
        $val = str_replace(',', '', $val);
        if (substr($val, 0, 5) == '-9999') {
            $val = substr($val, 5);
            while ((strlen($val) > 0) && (substr($val, 0, 1) == '9')) {
                $val = substr($val, 1);
            }
        }
        return $val;
    }

    private function getRegexForDate($dateFormat)
    {
        if (!$dateFormat) {
            throw new SupplierInvoiceParserException(
                "Date field missing required attribute 'pattern'"
            );
        }

        $delimiters = preg_split('/[a-zA-Z]+/', $dateFormat);
        array_shift($delimiters); /* remove empty string from front */
        $formatChars = preg_split('/[^a-zA-Z]+/', $dateFormat);
        $regexParts = [];
        foreach ($formatChars as $char) {
            switch ($char) {
                case 'd':
                case 'm':
                case 'y':
                    $regexParts[] = '\d{2}';
                    break;
                case 'j':
                case 'n':
                    $regexParts[] = '\d{1,2}';
                    break;
                case 'M':
                    $regexParts[] = '\w{3}';
                    break;
                case 'F':
                    $regexParts[] = '\w{3,9}';
                    break;
                case 'Y':
                    $regexParts[] = '\d{4}';
                    break;
                default:
                    throw new SupplierInvoiceParserException(
                        "Unknown date format string $char"
                    );
            }
        }
        $regex = '';
        foreach ($regexParts as $idx => $part) {
            $delim = preg_quote($delimiters[$idx], '/');
            $regex .= $part . $delim;
        }
        return sprintf('/\b(%s)\b/', $regex);
    }

    private function getRegexForFloat()
    {
        return sprintf('((%1$s\.\d*)|(\.\d*)|(%1$s))', $this->getRegexForInt());
    }

    private function getRegexForInt()
    {
        return '\-{0,1}[\d,]+';
    }
}
