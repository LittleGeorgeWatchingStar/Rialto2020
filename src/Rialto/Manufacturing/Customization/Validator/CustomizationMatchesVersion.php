<?php

namespace Rialto\Manufacturing\Customization\Validator;

use Rialto\Stock\VersionedItem;
use Symfony\Component\Validator\Constraint;


/**
 * Makes sure the reference designators in each substitution in the customization
 * are compatible with the version being customized.
 *
 * @Annotation
 * @see VersionedItem
 * @see CustomizationMatchesVersionValidator
 */
class CustomizationMatchesVersion extends Constraint
{
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
