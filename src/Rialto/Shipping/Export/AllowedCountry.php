<?php

namespace Rialto\Shipping\Export;

use Symfony\Component\Validator\Constraint;

/**
 * Validates that all of the items in a sales order are allowed to go
 * to the destination country.
 *
 * @Annotation
 */
class AllowedCountry extends Constraint
{
    public $message = 'Item item is not allowed to ship to country.';

    public function validatedBy()
    {
        return AllowedCountryValidator::class;
    }

    public function getTargets()
    {
        /* Applies to the entire SalesOrder class. */
        return self::CLASS_CONSTRAINT;
    }

}
