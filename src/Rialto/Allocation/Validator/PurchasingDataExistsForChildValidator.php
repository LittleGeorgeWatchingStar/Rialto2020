<?php

namespace Rialto\Allocation\Validator;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Manufacturing\Allocation\CanCreateChild;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Ensures that purchasing data exists for the child item of a WorkOrder.
 */
class PurchasingDataExistsForChildValidator extends ConstraintValidator
{
    /** @var PurchasingDataRepository */
    private $repo;

    public function __construct(ObjectManager $om)
    {
        $this->repo = $om->getRepository(PurchasingData::class);
    }

    /**
     * @param CanCreateChild $template
     * @param PurchasingDataExistsForChild $constraint
     */
    public function validate($template, Constraint $constraint)
    {
        if (!$template instanceof CanCreateChild) {
            throw new UnexpectedTypeException($template, CanCreateChild::class);
        }
        if (!$template->isCreateChild()) {
            return;
        }
        $childItem = $template->getChildItem();

        if (!$childItem) {
            $this->context->addViolation($constraint->message);
            return;
        }
        $location = $template->getBuildLocation();
        $version = $template->getChildVersion();
        $purchData = $this->repo->findPreferredByLocationAndVersion($location, $childItem, $version);

        if (!$purchData) {
            $this->context->addViolation($constraint->message, [
                'childItem' => $childItem->getSku(),
                'version' => $version,
                'location' => $location->getName(),
            ]);
        }
    }
}
