<?php


namespace Rialto\Madison\Feature;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Rialto\Madison\Feature\Repository\StockItemFeatureRepository;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Stock\Item\StockItem;

class FeatureInjector
{
    /** @var EntityManager */
    private $dbm;

    /** @var StockItemFeatureRepository */
    private $repo;

    public function __construct(EntityManager $dbm,
                                StockItemFeatureRepository $repo)
    {
        $this->dbm = $dbm;
        $this->repo = $repo;
    }

    public function setManufacturer(StockItem $item, Manufacturer $manufacturer)
    {
        $feature = $this->getOrAddFeatureWithCode($item, 'manufacturer');
        $feature->setValue($manufacturer->getName());
        $this->dbm->persist($feature);
    }

    private function getOrAddFeatureWithCode(StockItem $item, string $code): StockItemFeature
    {
        if ($feature = $this->findFirstFeatureWithCode($item, $code)) {
            return $feature;
        } else {
            $feature = new StockItemFeature($item, $code);
            $features[] = $feature;
            return $feature;
        }
    }

    /**
     * // TODO: PHP 7.2
     * @return StockItemFeature|null
     */
    private function findFirstFeatureWithCode(StockItem $item, string $code)
    {
        $features = new ArrayCollection($this->repo->findByItem($item));

        $first = $features->filter(function (StockItemFeature $f) use ($code) {
            return $f->getFeatureCode() === $code;
        })->first();

        return $first ? $first : null;
    }
}