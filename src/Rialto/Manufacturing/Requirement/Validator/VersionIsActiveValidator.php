<?php

namespace Rialto\Manufacturing\Requirement\Validator;


use Rialto\Stock\VersionedItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class VersionIsActiveValidator extends ConstraintValidator
{
    /**
     * @param VersionedItem $value
     */
    public function validate($value, Constraint $constraint)
    {
        assertion($value instanceof VersionedItem);
        $item = $value->getStockItem();
        $version = $value->getVersion();
        if ($item->hasVersion($version)) {
            $itemVersion = $item->getVersion($version);
            if (! $itemVersion->isActive()) {
                $this->context->addViolation("Version '$version' is not active.");
            }
        }
    }

}
