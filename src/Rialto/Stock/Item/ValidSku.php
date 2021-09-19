<?php

namespace Rialto\Stock\Item;

use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\RegexValidator;

/**
 * Ensures that a SKU contains only allowed characters.
 *
 * @Annotation
 */
class ValidSku extends Regex
{
    const REGEX_BARE =   '[A-Z]+[A-Z0-9]*';
    const REGEX_FULL = '/^[A-Z]+[A-Z0-9]*$/'; // TODO: php5.6

    public $pattern = self::REGEX_FULL;

    public $message = 'sku.regex';

    public function validatedBy()
    {
        return RegexValidator::class;
    }

    public function getRequiredOptions()
    {
        return [];
    }
}
