<?php

namespace Rialto\Manufacturing\WorkOrder\Validator;

use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Symfony\Component\Validator\Constraint;


/**
 * Ensures that the parent work order is compatible with the child.
 *
 * @Annotation
 * @see WorkOrder
 */
class ParentIsCompatible extends Constraint
{
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
