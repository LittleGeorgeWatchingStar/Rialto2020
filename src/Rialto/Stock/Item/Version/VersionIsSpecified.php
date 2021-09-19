<?php

namespace Rialto\Stock\Item\Version;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to ensure that a given version is specified.
 *
 * @Annotation
 */
class VersionIsSpecified extends Constraint
{
    public $message = 'Version must be specified';
}
