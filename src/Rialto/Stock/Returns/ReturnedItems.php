<?php

namespace Rialto\Stock\Returns;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Returns\Problem\AutoResolveLimits;
use Rialto\Stock\Returns\Problem\ItemResolution;
use Rialto\Stock\Returns\Problem\ReturnedItemResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Manages the process of checking in unused parts that have been returned from
 * a manufacturer.
 */
class ReturnedItems
{
    /**
     * The location to which the items are being returned. Typically the
     * warehouse.
     * @var Facility
     */
    private $returnedTo;

    /**
     * The location from which the items are coming. Typically a CM.
     * @var Facility
     * @Assert\NotNull
     */
    private $source;

    /**
     * @var ReturnedItem[]
     * @Assert\Valid(traverse=true)
     */
    private $items;

    public function __construct(Facility $returnedTo)
    {
        $this->returnedTo = $returnedTo;
        $this->items = new ArrayCollection();
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource(Facility $source)
    {
        $this->source = $source;
    }

    /** @return Facility */
    public function getDestination()
    {
        return $this->returnedTo;
    }

    /** @return StockBin[] */
    private function getBins()
    {
        return $this->items->filter(function (ReturnedItem $i) {
            return $i->hasBin();
        })->map(function (ReturnedItem $i) {
            return $i->getBin();
        })->toArray();
    }

    /**
     * @Assert\Callback(groups={"flow_ReturnedItems_step1"})
     */
    public function validateBinLocations(ExecutionContextInterface $context)
    {
        foreach ($this->getBins() as $bin) {
            if ($bin->isInTransit()) {
                continue;
            }
            if (!$bin->isAtLocation($this->source)) {
                $src = $this->source;
                $loc = $bin->getLocation();
                $context->buildViolation("$bin is at $loc, not $src.")
                    ->atPath('items')
                    ->addViolation();
            }
        }
    }

    /** @return ReturnedItem[] */
    public function getItems()
    {
        return $this->items;
    }

    public function addItem(ReturnedItem $item)
    {
        foreach ($this->items as $existing) {
            if ($item->equals($existing)) {
                return;
            }
        }
        $item->setLocations($this->source, $this->returnedTo);
        $this->items[] = $item;
    }

    public function removeItem(ReturnedItem $item)
    {
        $this->items->removeElement($item);
    }

    /**
     * Automatically resolve any small problems.
     */
    public function resolveSmallProblems(ReturnedItemResolver $resolver)
    {
        $limits = new AutoResolveLimits();
        foreach ($this->getItemsWithProblems() as $item) {
            $resolution = new ItemResolution($item, $limits);
            $resolver->resolveItem($resolution);
        }
    }

    /** @return ReturnedItem[] */
    public function getItemsWithoutProblems()
    {
        return $this->items->filter(function (ReturnedItem $i) {
            return !$i->hasProblems();
        })->getValues();
    }

    /** @return ReturnedItem[] */
    public function persistItemsWithProblems(ObjectManager $om)
    {
        $items = $this->getItemsWithProblems();
        foreach ($items as $item) {
            $om->persist($item);
        }
        return $items;
    }

    /** @return ReturnedItem[] */
    private function getItemsWithProblems()
    {
        return $this->items->filter(function (ReturnedItem $i) {
            return $i->hasProblems();
        })->toArray();
    }
}
