<?php

namespace Rialto\Accounting\Bank\Account;


use Symfony\Component\Validator\Constraint;

/**
 * Ensures that the cheque number is available.
 *
 * @Annotation
 */
class AvailableChequeNumber extends Constraint
{
    public $message = "Cheque number _chequeNo for _account has already been used.";

    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
