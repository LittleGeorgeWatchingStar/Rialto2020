<?php

namespace Rialto\Stock\Item\Version;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint to ensure that a given version is specified.
 */
class VersionIsSpecifiedValidator extends ConstraintValidator
{
    /**
     * @param Version|string $value
     * @param VersionIsSpecified $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ( is_string($value) ) {
            $value = new Version($value);
        }
        if (! $value instanceof Version ) {
            throw new \UnexpectedValueException("$value is not a valid version");
        }

        if (! $value->isSpecified() ) {
            $this->context->addViolation($constraint->message);
        }
    }
}
