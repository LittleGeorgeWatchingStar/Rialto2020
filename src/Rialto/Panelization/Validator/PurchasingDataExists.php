<?php

namespace Rialto\Panelization\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * Ensures that there is a PurchasingData record for the requested location
 * for each board in a panelization.
 *
 * @Annotation
 */
class PurchasingDataExists extends Constraint
{
    public $message = 'panelization.no_purch_data';

    public function validatedBy()
    {
        return PurchasingDataExistsValidator::class;
    }

    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
