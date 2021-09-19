<?php

namespace Rialto\Stock\Cost;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures that the stock item's standard cost is set.
 *
 * @Annotation
 */
class StandardCostIsSet extends Constraint
{
    public $message = 'Standard cost of _item is not set.';
}
