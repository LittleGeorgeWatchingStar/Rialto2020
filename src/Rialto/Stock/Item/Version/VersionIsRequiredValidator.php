<?php

namespace Rialto\Stock\Item\Version;

use Symfony\Component\Validator\Constraint;

/**
 * @see VersionIsRequired
 */
class VersionIsRequiredValidator extends VersionIsSpecifiedValidator
{
    /**
     * @param string $value
     * @param VersionIsRequired $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $value = new Version($value);
        parent::validate($value, $constraint);

        /* @var $value Version */
        if ( $value->isNone() ) {
            $this->context->addViolation($constraint->message);
        }
    }
}
