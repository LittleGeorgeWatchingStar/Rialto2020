<?php

namespace Rialto\Allocation\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures that purchsing data exists for the child items of
 * a WorkOrderCreation.
 *
 * @Annotation
 */
class PurchasingDataExistsForChild extends Constraint
{
    public $message = 'Cannot create child work order for childItem because no purchasing data exists for rev. version at location.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
