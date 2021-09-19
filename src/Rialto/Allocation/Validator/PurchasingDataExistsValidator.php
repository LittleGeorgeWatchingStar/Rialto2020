<?php

namespace Rialto\Allocation\Validator;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

/**
 * Ensures that there is a purchasing data record to match the given
 * Requirement.
 *
 * @see Requirement
 */
class PurchasingDataExistsValidator extends ConstraintValidator
{
    /** @var PurchasingDataRepository */
    private $repo;

    public function __construct(ObjectManager $om)
    {
        $this->repo = $om->getRepository(PurchasingData::class);
    }

    /**
     * @param Requirement $value
     * @param PurchasingDataExists $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (! $value instanceof Requirement ) {
            throw new UnexpectedValueException(sprintf(
                '%s validator can only be used to validate instances of Requirement',
                get_class($this)
            ));
        }

        if (! $this->hasPurchasingData($value) ) {
            $this->context->addViolation($constraint->message);
        }
    }

    private function hasPurchasingData(Requirement $requirement)
    {
        return (bool) $this->repo->findPreferredForRequirement($requirement);
    }

}
