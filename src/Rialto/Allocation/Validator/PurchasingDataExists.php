<?php

namespace Rialto\Allocation\Validator;

use Rialto\Allocation\Requirement\Requirement;
use Symfony\Component\Validator\Constraint;

/**
 * Ensures that there is a purchasing data record to match the given
 * Requirement.
 *
 * @see Requirement
 * @Annotation
 */
class PurchasingDataExists extends Constraint
{
    public $message = 'No purchasing data matches the requirements.';
}
