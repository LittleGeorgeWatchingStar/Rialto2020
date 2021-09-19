<?php

namespace Rialto\Allocation\Source;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Requirement\RequirementInterface;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Producer\Orm\StockProducerRepository;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\VersionedItem;

/**
 * A collection of homogeneous (same SKU) stock sources from which
 * a requirement can allocate stock.
 */
class SourceCollection
{
    /** @var BasicStockSource[] */
    private $sources;

    /** @var BasicStockSource[] $sources */
    public function __construct(array $sources)
    {
        $this->sources = $sources;
    }

    /** @return BasicStockSource[] */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * @param string $id
     * @return BasicStockSource[]
     */
    public function getSourcesById(string $id)
    {
        $result = [];
        foreach ($this->sources as $basicStockSource){
            if ($basicStockSource instanceof StockBin) {
                if ($basicStockSource->getId() === $id) {
                    array_push($result, $basicStockSource);
                }
            } elseif ($basicStockSource instanceof StockProducer) {
                if ($basicStockSource->getId() === $id) {
                    array_push($result, $basicStockSource);
                }
            }
        }
        return $result;
    }

    /**
     * @param string $id
     */
    public function removeSourcesById(string $id)
    {
        $search = [];
        foreach ($this->sources as $basicStockSource){
            if ($basicStockSource instanceof StockBin) {
                if ($basicStockSource->getId() === $id) {
                    array_push($search, $basicStockSource);
                    $this->sources = array_diff($this->sources, $search);                }
            } elseif ($basicStockSource instanceof StockProducer) {
                if ($basicStockSource->getId() === $id) {
                    array_push($search, $basicStockSource);
                    $this->sources = array_diff($this->sources, $search);
                }
            }
        }
    }

    /**
     * Factory method.
     */
    public static function fromAvailableBins(
        VersionedItem $requirement,
        Facility $location,
        ObjectManager $dbm)
    {
        /** @var StockBinRepository $repo */
        $repo = $dbm->getRepository(StockBin::class);
        $sources = $repo->createBuilder()
            ->available()
            ->allocatable()
            ->notUnresolved()
            ->byRequirement($requirement)
            ->atFacility($location)
            ->getResult();
        return new self($sources);
    }

    /**
     * Factory method.
     */
    public static function fromOpenOrders(RequirementInterface $requirement,
                                          DbManager $dbm)
    {
        /** @var $repo StockProducerRepository */
        $repo = $requirement->getStockItem()->getProducerRepository($dbm);
        $sources = $repo->createBuilder()
            ->openForAllocation()
            ->canAutoAllocate()
            ->forVersionedItem($requirement)
            ->byRequirementLocation($requirement)
            ->getResult();
        return new self($sources);
    }

    public function getQtyRemaining()
    {
        $total = 0;
        foreach ($this->sources as $source) {
            $total += $source->getQtyRemaining();
        }
        return $total;
    }

    public function getQtyAvailableTo(RequirementCollection $requirements)
    {
        $total = 0;
        foreach ($this->sources as $source) {
            $total += $source->getQtyAvailableTo($requirements);
        }
        return $total;
    }

    public function toArray()
    {
        return $this->sources;
    }
}
