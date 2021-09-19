<?php

namespace Rialto\Stock\Cost;

use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

/**
 * @see StandardCostIsSet
 */
class StandardCostIsSetValidator extends ConstraintValidator
{
    /**
     * @param StockItem $item
     * @param StandardCostIsSet $constraint
     */
    public function validate($item, Constraint $constraint)
    {
        if (! $item instanceof StockItem ) {
            throw new UnexpectedValueException("Expected instance of StockItem");
        }

        if ( $item->getStandardCost() <= 0 ) {
            $this->context->addViolation($constraint->message, [
                '_item' => $item,
            ]);
        }
    }
}
