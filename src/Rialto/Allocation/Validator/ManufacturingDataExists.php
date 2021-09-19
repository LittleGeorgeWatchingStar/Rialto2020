<?php

namespace Rialto\Allocation\Validator;

use Rialto\Allocation\Requirement\Requirement;
use Symfony\Component\Validator\Constraint;

/**
 * Ensures that the necessary manufacturing data exists to create a new
 * work order for the requirement.
 *
 * @see Requirement
 * @Annotation
 */
class ManufacturingDataExists
extends Constraint
{

}
