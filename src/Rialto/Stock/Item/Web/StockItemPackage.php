<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\FitFinder;
use Rialto\Stock\Item\NewSku;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\ValidSku;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Orm\StockItemPackageGateway;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use UnexpectedValueException;

/**
 * Handles form input to create a package for a board.
 */
class StockItemPackage
{
    const FIT_TOLERANCE = 0.5; // cm

    /**
     * The item for which we are creating a package.
     *
     * @var StockItem
     */
    private $child;

    /**
     * The version of the board that we will use in the package.
     *
     * @var ItemVersion
     * @Assert\NotNull
     */
    private $board;

    /**
     * The SKU of the new package item.
     *
     * @ValidSku
     * @NewSku
     */
    public $sku;

    /**
     * The category of the new package item.
     *
     * @var StockCategory
     */
    private $category;

    /** @var WorkType */
    private $workType;

    /**
     * The box, if any, in the new package's BOM.
     * @var ItemVersion
     */
    public $box = null;

    /**
     * The label, if any, in the new package's BOM.
     * @var StockItem
     */
    public $label = null;

    public function __construct(StockItem $child)
    {
        $this->child = $child;
        $this->board = $child->getAutoBuildVersion();
        $this->sku = $this->determineSku($child);
    }

    private function determineSku(StockItem $child): string
    {
        $matches = [];
        if (!preg_match('/([A-Z]+)(.*)/', $child->getSku(), $matches)) {
            throw new UnexpectedValueException("Unable to parse SKU $child");
        }
        $prefix = $this->determinePackagePrefix($matches[1]);
        $tail = $matches[2];

        return $prefix . $tail;
    }

    private function determinePackagePrefix($boardPrefix): string
    {
        switch ($boardPrefix) {
            case 'BRD':
                return 'PKG';
            case 'GS':
                return 'GUM';
            default:
                throw new UnexpectedValueException(
                    "Unable to determine package prefix for $boardPrefix");
        }
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return ItemVersion[] */
    public function getValidBoards(): array
    {
        return $this->child->getActiveVersions();
    }

    public function getBoard(): ItemVersion
    {
        return $this->board;
    }

    public function setBoard(ItemVersion $board)
    {
        $this->board = $board;
    }

    /** @Assert\Callback */
    public function validateBoxFits(ExecutionContextInterface $context)
    {
        if (!$this->box) {
            return;
        }
        if (null === $this->board->getDimensions()) {
            $context->buildViolation('%board% has no dimensions.')
                ->setParameter('%board%', $this->board->getFullSku())
                ->atPath('box')
                ->addViolation();
        } elseif (!$this->box->canContain($this->board, self::FIT_TOLERANCE)) {
            $box = $this->box->getFullSku();
            $context->buildViolation("$box is not big enough.")
                ->atPath('box')
                ->addViolation();
        }
    }

    public function loadComponents(StockItemPackageGateway $gateway)
    {
        $this->category = $gateway->getCategory();
        $this->label = $gateway->getDefaultLabel();
        $this->box = $this->findBestFittingBox($gateway);
        $this->workType = $gateway->getWorkType();
    }

    private function findBestFittingBox(StockItemPackageGateway $gateway)
    {
        if (null === $this->board->getDimensions()) {
            return null;
        }
        $eligible = $gateway->findEligibleBoxes();
        $finder = new FitFinder(self::FIT_TOLERANCE);
        return $finder->findClosestFit($eligible, $this->board);
    }

    /** @return StockItem */
    public function createPackage()
    {
        $parent = $this->child->copy($this->sku);
        $this->fixParentDescription($parent);
        $parent->setCategory($this->category);

        $package = $parent->addVersion($this->board->getVersionCode());
        $bomItem = $package->addComponent($this->child, 1);
        $bomItem->setVersion($this->board);
        $bomItem->setWorkType($this->workType);

        if ($this->label) {
            $package->addComponent($this->label, 1)
                ->setWorkType($this->workType)
                ->setVersion($this->label->getAutoBuildVersion());
        }

        if ($this->box) {
            $package->addComponent($this->box->getStockItem(), 1)
                ->setWorkType($this->workType);
            $package->setDimensions($this->box->getDimensions());
        } else {
            $package->setDimensions($this->board->getDimensions());
        }

        return $parent;
    }

    private function fixParentDescription(StockItem $parent)
    {
        $fixed = str_replace('(board)', '', $parent->getName());
        $parent->setName(trim($fixed));
    }
}
