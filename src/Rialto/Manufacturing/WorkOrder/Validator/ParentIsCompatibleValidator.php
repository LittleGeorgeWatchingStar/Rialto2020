<?php

namespace Rialto\Manufacturing\WorkOrder\Validator;

use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


/**
 * Ensures that the parent work order is compatible with the child.
 */
class ParentIsCompatibleValidator extends ConstraintValidator
{
    const WRONG_LOCATION = 'Work order locations do not match.';
    const QTY_MISMATCH = 'Parent has more units than child.';
    const PRODUCT_MISMATCH = 'BOM of {{ parent }} does not contain {{ child }}.';
    const VERSION_MISMATCH = 'Parent version is not compatible with child.';

    /**
     * @param WorkOrder $child
     * @param Constraint $constraint
     */
    public function validate($child, Constraint $constraint)
    {
        assertion($child instanceof WorkOrder);
        if (! $child->hasParent()) {
            return;
        }

        $parent = $child->getParent();
        if (! $parent->getLocation()->equals($child->getLocation())) {
            $this->context->addViolation(self::WRONG_LOCATION);
        }
        if ($parent->getQtyOrdered() > $child->getQtyOrdered()) {
            $this->context->addViolation(self::QTY_MISMATCH);
        }
        if (! $parent->hasRequirement($child)) {
            $this->context->addViolation(self::PRODUCT_MISMATCH, [
                '{{ parent }}' => $parent->getFullSku(),
                '{{ child }}' => $child->getFullSku(),
            ]);
        }
    }
}
