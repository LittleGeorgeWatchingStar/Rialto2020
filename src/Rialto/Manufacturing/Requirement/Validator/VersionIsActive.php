<?php

namespace Rialto\Manufacturing\Requirement\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * Ensure that the version of a requirement is active.
 *
 * @Annotation
 */
class VersionIsActive extends Constraint
{
    /**
     * Returns whether the constraint can be put onto classes, properties or
     * both.
     *
     * This method should return one or more of the constants
     * Constraint::CLASS_CONSTRAINT and Constraint::PROPERTY_CONSTRAINT.
     *
     * @return string|array One or more constant values
     */
    public function getTargets()
    {
        return [static::PROPERTY_CONSTRAINT, static::CLASS_CONSTRAINT];
    }

}
