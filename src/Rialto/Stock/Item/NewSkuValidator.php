<?php

namespace Rialto\Stock\Item;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NewSkuValidator extends ConstraintValidator
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        $existing = $this->om->find(StockItem::class, $value);
        if ($existing) {
            $this->context->buildViolation('sku.new')
                ->setParameter('%sku%', $value)
                ->addViolation();
        }
    }
}
