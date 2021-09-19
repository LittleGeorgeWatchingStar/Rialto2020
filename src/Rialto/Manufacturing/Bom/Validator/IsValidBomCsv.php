<?php

namespace Rialto\Manufacturing\Bom\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures that an uploaded file can be parsed into a valid bom csv file.
 *
 * @Annotation
 */
class IsValidBomCsv extends Constraint
{
    public $message = 'The file you uploaded is invalid';

    public function validatedBy()
    {
        return IsValidBomCsvValidator::class;
    }
}
