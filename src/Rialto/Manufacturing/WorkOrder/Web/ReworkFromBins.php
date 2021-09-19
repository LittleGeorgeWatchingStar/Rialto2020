<?php

namespace Rialto\Manufacturing\WorkOrder\Web;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Requirement\SingleRequirementCollection;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderCreation;
use Rialto\Manufacturing\WorkOrder\WorkOrderFactory;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Item\ManufacturedStockItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Allows the user to create a rework order by selecting bins to rework.
 */
class ReworkFromBins
{
    /**
     * @var StockBin[] The bins to rework.
     *
     * @Assert\Count(min=1, minMessage="rework.min_bins")
     */
    private $bins;

    /**
     * @var bool Whether the unit cost should be zero.
     */
    public $zeroCost = true;

    /**
     * @var PurchasingData
     *
     * @Assert\NotNull
     */
    public $purchData;

    public function __construct()
    {
        $this->bins = new ArrayCollection();
    }

    /**
     * @return StockBin[]
     */
    public function getBins()
    {
        return $this->bins;
    }

    public function addBin(StockBin $bin)
    {
        $this->bins[] = $bin;
    }

    public function removeBin(StockBin $bin)
    {
        $this->bins->removeElement($bin);
    }

    /**
     * @Assert\Callback
     */
    public function validateBinsMatch(ExecutionContextInterface $context)
    {
        $fullSku = null;
        foreach ($this->bins as $bin) {
            if (! $fullSku) {
                $fullSku = $bin->getFullSku();
            } elseif ($bin->getFullSku() != $fullSku) {
                $context->buildViolation("rework.matching_bins")
                    ->atPath('bins')
                    ->addViolation();
                return;
            }
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateVersionIsActive(ExecutionContextInterface $context)
    {
        if (count($this->bins) === 0) {
            return;
        }
        $item = $this->getItem();
        $version = $item->getVersion($this->getVersion());
        if (! $version->isActive()) {
            $context->buildViolation('rework.active_bin')
                ->setParameter('%version%', $version)
                ->atPath('bins')
                ->addViolation();
        }
    }

    /** @return ManufacturedStockItem */
    private function getItem()
    {
        foreach ($this->bins as $bin) {
            return $bin->getStockItem();
        }
        throw new \LogicException("No bins");
    }

    private function getVersion()
    {
        foreach ($this->bins as $bin) {
            return $bin->getVersion();
        }
        throw new \LogicException("No bins");
    }

    private function getCustomization()
    {
        foreach ($this->bins as $bin) {
            return $bin->getCustomization();
        }
        throw new \LogicException("No bins");
    }

    private function getTotalQty()
    {
        $total = 0;
        foreach ($this->bins as $bin) {
            $total += $bin->getQtyRemaining();
        }
        return $total;
    }

    /** @return WorkOrder */
    public function createOrder(WorkOrderFactory $factory)
    {
        $template = new WorkOrderCreation($this->getItem(), $this->getVersion());
        $template->setCustomization($this->getCustomization());
        $template->setPurchasingData($this->purchData);
        $template->setQtyOrdered($this->getTotalQty());
        $template->setCreateChild(false);

        $wo = $factory->createRework($template);
        if ($this->zeroCost) {
            $wo->initializeUnitCost(0);
        }
        return $wo;
    }

    public function allocateToOrder(WorkOrder $order, AllocationFactory $factory)
    {
        $this->clearExistingAllocations();
        $this->createNewAllocations($order, $factory);
    }

    private function clearExistingAllocations()
    {
        foreach ($this->bins as $bin) {
            foreach ($bin->getAllocations() as $alloc) {
                $alloc->close();
            }
        }
    }

    private function createNewAllocations(WorkOrder $order, AllocationFactory $factory)
    {
        foreach ($order->getRequirements() as $req) {
            $collection = new SingleRequirementCollection($req);
            $collection->setShareBins(false);
            $factory->allocate($collection, $this->bins->getValues());
        }
    }
}
