<?php

namespace Rialto\Stock\Item;

use Rialto\Measurement\Units;
use Rialto\Shipping\Export\HarmonizationCode;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\Version\ItemVersionTemplate;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Gathers and validates the information needed to create a new stock item.
 */
class StockItemTemplate
{
    /**
     * @ValidSku
     * @NewSku
     */
    private $stockCode;

    public $pattern;

    public $mbFlag;

    /**
     * @var StockCategory
     */
    public $category;

    /**
     * @Assert\NotBlank(message="Description cannot be blank.")
     * @Assert\Length(max=110,
     *   maxMessage="Description cannot be more than {{ limit }} characters long.")
     */
    public $name;

    /**
     * @Assert\NotBlank(message="Long description cannot be blank.",
     *   groups={"manual"})
     */
    public $longDescription;

    /**
     * @Assert\Length(max=50,
     *   maxMessage="Package cannot be more than {{ limit }} characters long.")
     */
    public $package;

    /**
     * @Assert\Length(max=20,
     *   maxMessage="Part value cannot be more than {{ limit }} characters long.")
     */
    public $partValue;

    /**
     * @var Units
     * @Assert\NotNull(message="Invalid units.")
     */
    public $units;

    /**
     * @Assert\Country(message="Country of origin is not a valid country code.")
     * @Assert\NotBlank(groups={"sellable"}, message="Country of origin is required.")
     */
    public $countryOfOrigin;

    /**
     * @Assert\Type(type="numeric", message="Order quantity must be a number.")
     */
    public $orderQuantity;

    /**
     * @var ItemVersionTemplate
     * @Assert\Valid
     */
    public $initialVersion;

    /**
     * @var HarmonizationCode
     * @Assert\NotNull(message="Harmonization code is required.",
     *   groups={"sellable"})
     */
    public $harmonizationCode;

    /**
     * @Assert\NotBlank(message="ECCN code is required.",
     *   groups={"sellable"})
     * @Assert\Regex(pattern="/,/", match=false, message="ECCN code cannot contain a comma.")
     */
    public $eccnCode;

    /**
     * @Assert\NotBlank(message="RoHS status is required.")
     * @Assert\Choice(callback={"Rialto\Stock\Item\RoHS", "getValid"},
     *     message="Invalid value for RoHS.",
     *     strict=true)
     */
    public $rohs;

    /**
     * @var boolean
     */
    public $closeCount = false;

    /**
     * @var TaxAuthority
     */
    public $taxAuthority;

    public function __construct()
    {
        $this->initialVersion = new ItemVersionTemplate();
    }

    public function getStockCode()
    {
        return $this->stockCode;
    }

    public function setStockCode($stockCode)
    {
        $this->stockCode = strtoupper(trim($stockCode));
    }

    /** @Assert\Callback */
    public function validateStockCodePattern(ExecutionContextInterface $context)
    {
        if (empty($this->stockCode) && empty($this->pattern)) {
            $context->buildViolation("Stock code is required.")
                ->atPath('stockCode')
                ->addViolation();
        } elseif ($this->stockCode && $this->pattern) {
            $context->buildViolation("Cannot specify both a stock code and a pattern.")
                ->atPath('pattern')
                ->addViolation();
        }
    }

    /** @Assert\Callback */
    public function validatePartValueAndFootprint(ExecutionContextInterface $context)
    {
        if ($this->partValue && $this->package) {
            return;
        }
        if (! $this->category) {
            $context->buildViolation("Category is required.")
                ->atPath('category')
                ->addViolation();
        }
        if (! in_array($this->mbFlag, StockItem::getValidStockTypes())) {
            $context->buildViolation("mbFlag is not valid.")
                ->atPath('mbFlag')
                ->addViolation();
        }
    }

    public function isSellable()
    {
        return $this->category ? $this->category->isSellable() : false;
    }

    public function isVersioned()
    {
        if ($this->hasSubcomponents()) {
            return true;
        }
        elseif ($this->category) {
            return $this->category->isPCB();
        }
        return false;
    }

    private function hasSubcomponents()
    {
        return in_array($this->mbFlag, [
            StockItem::MANUFACTURED,
            StockItem::ASSEMBLY,
        ]);
    }

    public function isPhysicalPart()
    {
        return in_array($this->mbFlag, [
            StockItem::PURCHASED,
            StockItem::MANUFACTURED,
        ]);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('pattern', new Assert\Regex([
            'pattern' => StockCodePattern::VALID,
        ]));
    }
}
