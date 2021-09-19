<?php

namespace Rialto\Manufacturing\Bom\Orm;

use Doctrine\Common\Collections\Collection;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Manufacturing\Bom\TurnkeyExclusion;
use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;

class TurnkeyExclusionRepository extends RialtoRepositoryAbstract
{
    /**
     * @param WorkOrder $workOrder
     * @param Component $component
     * @return boolean True if $component is provided by the manufacturer.
     */
    public function isTurnkeyComponent(WorkOrder $workOrder, Component $component)
    {
        $qb = $this->createQueryBuilder('te')
            ->select('count(distinct te.parent)')
            ->where('te.parent = :parent')
            ->andWhere('te.component = :component')
            ->andWhere('te.location = :location')
            ->setParameters([
                'parent' => $workOrder->getSku(),
                'component' => $component->getSku(),
                'location' => $workOrder->getLocation(),
            ]);
        $result = $qb->getQuery()->getSingleScalarResult();
        return $result == 0;

    }

    /**
     * @param Item $parent
     * @param Item $component
     * @param Facility $location
     * @return boolean
     *  True if the component is excluded from turnkey builds for the
     *  parent item at the location.
     */
    public function isExcluded(
        Item $parent,
        Item $component,
        Facility $location )
    {
        $qb = $this->createQueryBuilder('te')
            ->where('te.parent = :parent')
            ->andWhere('te.component = :component')
            ->andWhere('te.location = :location')
            ->setParameters([
                'parent' => $parent->getSku(),
                'component' => $component->getSku(),
                'location' => $location->getId()
            ]);
        $results = $qb->getQuery()->getResult();
        return count($results);
    }

    public function updateExclusions(StockItem $parent, Facility $location, Collection $components)
    {
        $exclusions = $this->findBy([
            'parent' => $parent->getSku(),
            'location' => $location->getId()
        ]);
        foreach ( $exclusions as $exclusion ) {
            if (! $components->contains($exclusion->getComponent())) {
                $this->_em->remove($exclusion);
            }
        }
        foreach ( $components as $component ) {
            foreach ( $exclusions as $exclusion ) {
                if ( $exclusion->equals($component) ) continue 2;
            }
            $exclusion = new TurnkeyExclusion($parent, $location, $component);
            $this->_em->persist($exclusion);
        }
    }
}
