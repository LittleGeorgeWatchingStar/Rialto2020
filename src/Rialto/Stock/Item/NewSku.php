<?php

namespace Rialto\Stock\Item;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures that a new stock code has not already been used.
 *
 * @Annotation
 */
class NewSku extends Constraint
{
    public function validatedBy()
    {
        return NewSkuValidator::class;
    }
}
