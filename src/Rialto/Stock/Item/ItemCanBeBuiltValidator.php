<?php

namespace Rialto\Stock\Item;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ItemCanBeBuiltValidator extends ConstraintValidator
{
    /**
     * @param StockItem $item The stock item
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($item, Constraint $constraint)
    {
        assertion($item instanceof StockItem);
        if (!$item->isManufactured()) {
            $this->context->addViolation("$item is not manufactured.");
        }
        if ($item->isDiscontinued()) {
            $this->context->addViolation("$item is discontinued.");
        }
        if (count($item->getActiveVersions()) == 0) {
            $this->context->addViolation("$item has no active versions.");
        } elseif (!$item->hasSpecifiedVersions()) {
            $this->context->addViolation("$item has no specified versions.");
        }
    }
}
