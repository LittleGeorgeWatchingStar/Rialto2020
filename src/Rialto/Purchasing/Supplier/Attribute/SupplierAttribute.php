<?php

namespace Rialto\Purchasing\Supplier\Attribute;

use Rialto\Entity\EntityAttribute;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Allows us to store arbitrary values associated with a supplier.
 *
 * @UniqueEntity(fields={"supplier", "attribute"},
 *     message="You cannot have the same attribute twice.")
 */
class SupplierAttribute extends EntityAttribute
{
    /**
     * A boolean attribute indicating that invoices emailed from this
     * supplier can be automatically imported.
     */
    const AUTO_IMPORT_EMAIL = 'auto_import_email';

    /**
     * The URL pattern for doing a product search on the supplier's website.
     */
    const SEARCH_URL = 'search_url';

    /** @var Supplier */
    private $supplier;

    /** @return string[] */
    public static function getChoices()
    {
        $attr = self::getValidAttributes();
        return array_combine($attr, $attr);
    }

    /** @return string[] */
    private static function getValidAttributes()
    {
        return [
            self::AUTO_IMPORT_EMAIL,
            self::SEARCH_URL,
        ];
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /** @return Supplier */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setEntity(RialtoEntity $entity)
    {
        $this->setSupplier($entity);
    }

    /**
     * @Assert\Callback
     */
    public function validateAttribute(ExecutionContextInterface $context)
    {
        switch ($this->getAttribute()) {
            case self::SEARCH_URL:
                if (! is_substring(':q', $this->getValue())) {
                    $context->addViolation('Search URL must contain ":q" as a query variable.');
                }
                break;
        }
    }
}
