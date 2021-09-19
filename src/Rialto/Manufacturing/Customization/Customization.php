<?php

namespace Rialto\Manufacturing\Customization;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Entity\RialtoEntity;
use Rialto\Stock\ItemIndex;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A customization is a named set of component substitutions for a
 * stock item.
 *
 * @UniqueEntity(fields={"name", "stockCodePattern"},
 *     message="A matching customization already exists.")
 */
class Customization implements RialtoEntity
{
    /** @var int */
    private $id;

    /**
     * @Assert\NotBlank(message="Name cannot be blank.")
     * @Assert\Length(max=64)
     */
    private $name = '';

    /**
     * This customization can be applied to any stock item whose stock code
     * matches this pattern. Use "%" as a wildcard.
     * @var string
     * @Assert\NotBlank(message="Stock code pattern cannot be blank.")
     * @Assert\Length(max=20)
     */
    private $stockCodePattern = '';

    /**
     * Which hard-coded customization strategies should be executed, in
     * addition to any substitutions.
     *
     * @var string[]
     */
    private $strategies = [];

    /**
     * @var Substitution[]
     * @Assert\Valid(traverse=true)
     * @Assert\Count(max=100)
     */
    private $substitutions;

    public static function areEqual(Customization $first = null, Customization $second = null)
    {
        if ($first) {
            return $first->equals($second);
        } elseif ($second) {
            return $second->equals($first);
        }
        return true; /* both null */
    }

    public function __construct()
    {
        $this->substitutions = new ArrayCollection();
    }

    /**
     * The default or 'null' customization for an Item is to do nothing at all.
     */
    public static function empty(): self
    {
        return new self();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function getStockCodeSuffix(Customization $cmz = null)
    {
        return ($cmz && $cmz->getId()) ? '-C' . $cmz->getId() : '';
    }

    public function getSubstitutions()
    {
        return $this->substitutions->toArray();
    }

    public function addSubstitution(Substitution $sub)
    {
        if (! $this->substitutions->contains($sub)) {
            $this->substitutions[] = $sub;
        }
    }

    public function removeSubstitution(Substitution $sub)
    {
        $this->substitutions->removeElement($sub);
    }

    /**
     * How much the unit price of a sales order item should be
     * adjusted if the item includes this customization.
     *
     * @return float
     */
    public function getPriceAdjustment()
    {
        $total = 0;
        foreach ($this->substitutions as $sub) {
            $total += $sub->getPriceAdjustment();
        }
        return $total;
    }

    public function setStockCodePattern($pattern)
    {
        $this->stockCodePattern = preg_replace('/[%*]+/', '%', trim($pattern));
    }

    public function getStockCodePattern()
    {
        return $this->stockCodePattern;
    }

    public function equals(Customization $other = null)
    {
        if (! $other) {
            return false;
        }
        return $this->id == $other->getId();
    }

    public function applySubstitutions(ItemIndex $bom)
    {
        foreach ($this->substitutions as $sub) {
            $sub->applyToBom($bom);
        }
    }

    /**
     * @return string[]
     */
    public function getStrategies()
    {
        return $this->strategies;
    }

    /**
     * @param string[] $strategies
     */
    public function setStrategies(array $strategies)
    {
        $this->strategies = $strategies;
    }
}
