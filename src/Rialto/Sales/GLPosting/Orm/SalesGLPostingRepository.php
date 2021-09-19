<?php

namespace Rialto\Sales\GLPosting\Orm;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Sales\Customer\SalesArea;
use Rialto\Sales\GLPosting\GLPosting;
use Rialto\Sales\GLPosting\SalesGLPosting;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Category\StockCategory;

class SalesGLPostingRepository extends RialtoRepositoryAbstract
{
    /**
     * @return GLPosting
     */
    public function findBestMatch(
        SalesArea $area,
        StockCategory $cat,
        SalesType $type )
    {
        $sql = "select pos.*
            from SalesGLPostings as pos
            where ifnull(pos.Area, '') in (:area, :any)
              and ifnull(pos.StkCat, '') in (:category, :any)
              and ifnull(pos.SalesType, '') in (:type, :any)
            order by isnull(pos.Area) ASC,
                     isnull(pos.StkCat) ASC,
                     isnull(pos.SalesType) ASC
            limit 1
        ";

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(
            SalesGLPosting::class, 'pos'
        );

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameters([
            'area' => $area->getId(),
            'category' => $cat->getId(),
            'type' => $type->getId(),
            'any' => '',
        ]);
        return $query->getSingleResult();
    }
}

