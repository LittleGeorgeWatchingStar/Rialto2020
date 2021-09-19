<?php

namespace Rialto\Stock\Item\Version;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to ensure that a given version is a regular version.
 *
 * @Annotation
 */
class VersionIsRequired extends Constraint
{
    public $message = 'Version is required';
}
