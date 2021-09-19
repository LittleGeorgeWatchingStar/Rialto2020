<?php

namespace Rialto\Allocation\Validator;

use Rialto\Allocation\Requirement\Requirement;
use Rialto\Stock\Item\ManufacturedStockItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

/**
 * Ensures that the necessary manufacturing data exists to create a new
 * work order for the requirement.
 */
class ManufacturingDataExistsValidator extends ConstraintValidator
{
    /**
     * @param Requirement $requirement
     * @param ManufacturingDataExists $constraint
     */
    public function validate($requirement, Constraint $constraint)
    {
        if (!$requirement instanceof Requirement) {
            throw new UnexpectedValueException(sprintf(
                '%s validator can only be used to validate instances of Requirement',
                get_class($this)
            ));
        }

        /** @var ManufacturedStockItem $item */
        $item = $requirement->getStockItem();
        if (!$item->isManufactured()) {
            return;
        }
        $bom = $item->getBom($requirement->getVersion());
        if (count($bom) == 0) {
            $this->context->addViolation('No BOM exists for _item-R_version', [
                '_item' => $item->getId(),
                '_version' => $requirement->getVersion(),
            ]);
        }
    }
}
