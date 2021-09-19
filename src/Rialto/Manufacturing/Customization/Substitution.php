<?php

namespace Rialto\Manufacturing\Customization;

use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\Component\Designators;
use Rialto\Manufacturing\Component\SimpleComponent;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\ItemIndex;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Represents a component substitution that is part of the process of
 * customizing a stock item.
 */
class Substitution implements RialtoEntity
{
    const TYPE_DNP = 'DNP';
    const TYPE_ADD = 'ADD';
    const TYPE_SWAP = 'SWAP';
    const TYPE_SWAP_ALL = 'SWAP-ALL';

    const FLAG_EXT_TEMP = 'ext-temp';

    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Type is required.")
     * @Assert\Choice(callback="getTypeOptions", strict=true)
     */
    private $type;

    /**
     * @Assert\NotBlank(message="Instructions cannot be blank.")
     */
    private $instructions;

    /**
     * Do Not Populate: list of designators to leave unpopulated.
     * @var string[]
     * @Assert\Count(max=10000)
     */
    private $dnpDesignators = [];

    /**
     * The item ommitted/removed from the $dnp designators.
     * @var PhysicalStockItem
     */
    private $dnpComponent;

    /**
     * List of designators to populate.
     * @var string[]
     * @Assert\Count(max=10000)
     */
    private $addDesignators = [];

    /**
     * The item with which to populate the $populate designators.
     * @var PhysicalStockItem
     */
    private $addComponent;

    /**
     * @Assert\Type(type="numeric", message="Price adjustment must be a number.")
     */
    private $priceAdjustment = 0;

    /** @var WorkType|null */
    private $workType;

    /**
     * @var string[]
     * @Assert\Count(max=100)
     */
    private $flags = [];

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = trim($type);
    }

    public static function getTypeOptions()
    {
        $types = [
            self::TYPE_DNP,
            self::TYPE_ADD,
            self::TYPE_SWAP,
            self::TYPE_SWAP_ALL,
        ];
        return array_combine($types, $types);
    }

    public function getIndexKey()
    {
        return $this->dnpComponent->getSku();
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    public function setInstructions($instructions)
    {
        $this->instructions = trim($instructions);
    }

    /**
     * The instructions and the components that are changed.
     * @return string
     */
    public function getLongDescription()
    {
        return sprintf('%s:%s%s',
            $this->instructions,
            $this->isDnp() ? " -{$this->dnpComponent}" : '',
            $this->isAddition() ? " +{$this->addComponent}" : ''
        );
    }

    /**
     * @return string[]
     */
    public function getDnpDesignators()
    {
        return $this->dnpDesignators;
    }

    public function setDnpDesignators(array $designators)
    {
        $this->dnpDesignators = Designators::normalize($designators);
    }

    /**
     * @return PhysicalStockItem|null
     */
    public function getDnpComponent()
    {
        return $this->dnpComponent;
    }

    public function setDnpComponent(PhysicalStockItem $component = null)
    {
        $this->dnpComponent = $component;
    }

    public function getDnpQuantity()
    {
        return count($this->dnpDesignators);
    }

    public function isDnp()
    {
        return in_array($this->type, [self::TYPE_DNP, self::TYPE_SWAP]);
    }

    /** @Assert\Callback */
    public function validateDnp(ExecutionContextInterface $context)
    {
        if (!$this->isDnp()) {
            return;
        }
        if (!$this->dnpComponent) {
            $context->buildViolation("Choose a DNP component.")
                ->atPath('dnpComponent')
                ->addViolation();
        }
        if ($this->getDnpQuantity() == 0) {
            $context->buildViolation("Enter DNP designators.")
                ->atPath('dnpDesignators')
                ->addViolation();
        }
    }

    public function applyToBom(ItemIndex $bom)
    {
        if ($this->isDnp()) {
            $this->applyDnp($bom);
        }
        if ($this->isAddition()) {
            $this->applyAdd($bom);
        }
        if ($this->isSwapAll()) {
            $this->applySwapAll($bom);
        }
    }

    private function applyDnp(ItemIndex $bom)
    {
        $original = $bom->get($this->dnpComponent);
        assertion(null != $original, "BOM does not contain {$this->dnpComponent}");

        $newQty = $original->getUnitQty() - $this->getDnpQuantity();
        $newDes = Designators::setDiff($original->getDesignators(), $this->dnpDesignators);
        if ($newQty > 0) {
            $bom->add(UpdatedComponent::fromExistingComponent($original, $newQty, $newDes));
        } else {
            $bom->remove($original);
        }
    }

    private function applyAdd(ItemIndex $bom)
    {
        $original = $bom->get($this->addComponent) ?: UpdatedComponent::fromNewComponent($this, 0, []);
        $newQty = $original->getUnitQty() + $this->getAddQuantity();
        assertion($newQty > 0);
        $newDes = Designators::setUnion($original->getDesignators(), $this->addDesignators);
        $bom->add(UpdatedComponent::fromExistingComponent($original, $newQty, $newDes));
    }

    private function applySwapAll(ItemIndex $bom)
    {
        /** @var $remove Component */
        $remove = $bom->get($this->dnpComponent);
        assertion(null != $remove, "BOM does not contain {$this->dnpComponent}");
        /** @var $update Component */
        $update = $bom->get($this->addComponent) ?: UpdatedComponent::fromNewComponent($this, 0, []);
        $newQty = $remove->getUnitQty() + $update->getUnitQty();
        assertion($newQty > 0);
        $newDes = Designators::setUnion($remove->getDesignators(), $update->getDesignators());
        $bom->add(UpdatedComponent::fromExistingComponent($update, $newQty, $newDes));
        $bom->remove($remove);
    }

    /**
     * @return string[]
     */
    public function getAddDesignators()
    {
        return $this->addDesignators;
    }

    public function setAddDesignators(array $designators)
    {
        $this->addDesignators = Designators::normalize($designators);
    }

    /**
     * @return PhysicalStockItem|null
     *  The item that will replace the original component, if any.
     */
    public function getAddComponent()
    {
        return $this->addComponent;
    }

    public function setAddComponent(PhysicalStockItem $substitute = null)
    {
        $this->addComponent = $substitute;
    }

    public function getAddQuantity()
    {
        return count($this->addDesignators);
    }

    public function isAddition()
    {
        return in_array($this->type, [self::TYPE_ADD, self::TYPE_SWAP]);
    }

    /**
     * The version of the substitute item.
     * @return Version
     */
    public function getSubstituteVersion()
    {
        return $this->addComponent->getAutoBuildVersion();
    }

    /** @Assert\Callback */
    public function validateAdd(ExecutionContextInterface $context)
    {
        if (!$this->isAddition()) {
            return;
        }
        if (!$this->addComponent) {
            $context->buildViolation("Choose a component to add.")
                ->atPath('addComponent')
                ->addViolation();
        }
        if ($this->getAddQuantity() == 0) {
            $context->buildViolation('Enter Add designators.')
                ->atPath('addDesignators')
                ->addViolation();
        }
    }

    public function isSwapAll()
    {
        return self::TYPE_SWAP_ALL == $this->type;
    }

    /** @Assert\Callback */
    public function validateSwapAll(ExecutionContextInterface $context)
    {
        if (!$this->isSwapAll()) {
            return;
        }
        if (!$this->dnpComponent) {
            $context->buildViolation('Choose a DNP component.')
                ->atPath('dnpComponent')
                ->addViolation();
        }
        if ($this->getDnpQuantity() > 0) {
            $context->buildViolation('Do not specify designators for SWAP-ALL.')
                ->atPath('dnpDesignators')
                ->addViolation();
        }
        if (!$this->addComponent) {
            $context->buildViolation('Choose a component to add.')
                ->atPath('addComponent')
                ->addViolation();
        }
        if ($this->getAddQuantity() > 0) {
            $context->buildViolation('Do not specify designators for SWAP-ALL.')
                ->atPath('addDesignators')
                ->addViolation();
        }
    }

    /**
     * Contributes to the price adjustment of a customization.
     *
     * @see Customization->getPriceAdjustment()
     * @return float
     */
    public function getPriceAdjustment()
    {
        return $this->priceAdjustment;
    }

    public function setPriceAdjustment($adjustment)
    {
        $this->priceAdjustment = (float) $adjustment;
    }

    public function __toString()
    {
        return "substitution " . $this->id;
    }

    /**
     * @return WorkType
     */
    public function getWorkType()
    {
        return $this->workType;
    }

    public function setWorkType(WorkType $workType = null)
    {
        $this->workType = $workType;
    }

    /** @Assert\Callback */
    public function validateWorkType(ExecutionContextInterface $context)
    {
        if ($this->isAddition() && (!$this->workType)) {
            $context->buildViolation("Work type is required for {$this->type}.")
                ->atPath('workType')
                ->addViolation();
        }
    }

    /**
     * @return string[]
     */
    public function getFlags()
    {
        return $this->flags;
    }

    public function addFlag($flag)
    {
        $this->flags[] = $flag;
        $this->flags = array_values(array_unique($this->flags));
    }

    public function removeFlag($flag)
    {
        $index = array_search($flag, $this->flags);
        if (false !== $index) {
            unset($this->flags[$index]);
            $this->flags = array_values($this->flags);
        }
    }

    public static function getFlagOptions()
    {
        return [
            'Extended temperature' => self::FLAG_EXT_TEMP,
        ];
    }
}

/**
 * A component after a substitution has been applied to it.
 */
class UpdatedComponent extends SimpleComponent
{
    public static function fromExistingComponent(Component $original, $unitQty, array $designators)
    {
        $updated = new self($original->getStockItem(), $unitQty, $designators);
        $updated->version = $original->getVersion();
        $updated->customization = $original->getCustomization();
        $updated->workType = $original->getWorkType();
        return $updated;
    }

    public static function fromNewComponent(Substitution $sub, $unitQty, array $designators)
    {
        $updated = new self($sub->getAddComponent(), $unitQty, $designators);
        $updated->version = $sub->getSubstituteVersion();
        $updated->workType = $sub->getWorkType();
        return $updated;
    }
}
