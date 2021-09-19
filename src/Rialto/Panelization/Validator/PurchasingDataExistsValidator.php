<?php

namespace Rialto\Panelization\Validator;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Panelization\Panelizer;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PurchasingDataExistsValidator extends ConstraintValidator
{
    /** @var PurchasingDataRepository */
    private $repo;

    public function __construct(ObjectManager $om)
    {
        $this->repo = $om->getRepository(PurchasingData::class);
    }

    /**
     * @param Panelizer $panelizer
     * @param PurchasingDataExists $constraint
     */
    public function validate($panelizer, Constraint $constraint)
    {
        assertion($panelizer instanceof Panelizer);

        $supplier = $panelizer->getBoardSupplier();
        foreach ($panelizer->getVersions() as $version) {
            if (!$this->hasPurchasingData($supplier, $version)) {
                $this->context->addViolation($constraint->message, [
                    '{{item}}' => $version->getFullSku(),
                    '{{supplier}}' => $supplier->getName(),
                ]);
            }
        }
    }

    private function hasPurchasingData(Supplier $supplier, ItemVersion $version)
    {
        return (bool) $this->repo->findPreferredBySupplierAndVersion(
            $supplier,
            $version->getStockItem(),
            $version);
    }
}
