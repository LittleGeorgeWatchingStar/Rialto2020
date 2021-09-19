<?php

namespace Rialto\Purchasing\Catalog\Template;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Catalog\Orm;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A purchasing data "template" describes how to automatically create
 * purchasing data records.
 *
 * @see PurchasingDataStratey
 */
class PurchasingDataTemplate implements RialtoEntity
{
    private $id;

    /**
     * @var Supplier
     * @Assert\NotNull
     */
    private $supplier;

    /**
     * @var BinStyle
     * @Assert\NotNull
     */
    private $binStyle;

    /**
     * @var int
     * @Assert\NotBlank
     * @Assert\Range(min=1)
     */
    private $binSize;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $strategy;

    /**
     * @Assert\Type(type="integer",
     *   message="Increment qty must be an integer.")
     * @Assert\Range(min=1,
     *   minMessage="Increment quantity must be at least {{ min }}.")
     */
    private $incrementQty;

    /** @var array[string $name]float */
    private $variables;

    public function __construct()
    {
        $this->variables = [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /** @return BinStyle */
    public function getBinStyle()
    {
        return $this->binStyle;
    }

    public function setBinStyle(BinStyle $binStyle)
    {
        $this->binStyle = $binStyle;
    }

    /**
     * @return int
     */
    public function getBinSize()
    {
        return $this->binSize;
    }

    /**
     * @param int $binSize
     */
    public function setBinSize($binSize)
    {
        $this->binSize = $binSize;
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    public function setStrategy(string $strategy)
    {
        $this->strategy = $strategy;
    }

    public function getStrategyInstance(): PurchasingDataStrategy
    {
        return PurchasingDataStrategy::create($this->strategy);
    }

    /**
     * @return int
     *  Parts must be ordered in multiples of this quantity.
     */
    public function getIncrementQty()
    {
        return $this->incrementQty;
    }

    public function setIncrementQty($qty)
    {
        $this->incrementQty = $qty;
    }

    /**
     * @return string[]
     */
    public function getVariableNames(): array
    {
        if (!$this->strategy) {
            return [];
        }
        return $this->getStrategyInstance()->getVariableNames();
    }

    /**
     * @return string[]
     */
    public function getVariableNFormTypes(): array
    {
        if (!$this->strategy) {
            return [];
        }
        return $this->getStrategyInstance()->getVariableFormTypes();
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables)
    {
        $this->variables = $variables;
    }

    /** @Assert\Callback */
    public function validateVariables(ExecutionContextInterface $context)
    {
        if (!$this->getStrategy()) {
            return;
        }
        $constraints = $this->getStrategyInstance()->getVariableConstraints();
        $validator = $context->getValidator()->inContext($context);

        foreach ($this->getVariableNames() as $index => $name) {
            if (!isset($this->variables[$name])) {
                $context->buildViolation('purchasing.purch_data_template.variable')
                    ->setParameter('%name%', $name)
                    ->addViolation();
            } else {
                $constraint = $constraints[$name];
                $validator
                    ->validate($this->variables[$name], $constraint);
            }
        }
    }

    public function alreadyExists(ItemVersion $version, ObjectManager $dbm)
    {
        $item = $version->getStockItem();
        $repo = $dbm->getRepository(PurchasingData::class);
        /* @var $repo Orm\PurchasingDataRepository */
        return $repo->exists($this->supplier, $item, $version);
    }

    public function appliesTo(StockItem $item)
    {
        return $this->getStrategyInstance()->appliesTo($item);
    }

    /** @return PurchasingData */
    public function createFor(ItemVersion $version)
    {
        return $this->getStrategyInstance()->createPurchasingData($this, $version);
    }

}
