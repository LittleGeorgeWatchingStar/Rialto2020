<?php

namespace Rialto\Sales\GLPosting\Orm;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Sales\Customer\SalesArea;
use Rialto\Sales\GLPosting\CogsGLPosting;
use Rialto\Sales\GLPosting\GLPosting;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Category\StockCategory;

class CogsGLPostingRepository extends RialtoRepositoryAbstract
{
    const AREA_ANY = 'AN';
    const CATEGORY_ANY = 'ANY';
    const TYPE_ANY = 'AN';

    /**
     * @return GLPosting
     */
    public function findBestMatch(
        SalesArea $area,
        StockCategory $cat,
        SalesType $type )
    {
        $sql = "select pos.* from COGSGLPostings as pos
            where pos.Area in (:areaId1, :areaId2)
            and pos.StkCat in (:cat1, :cat2)
            and pos.SalesType in (:type1, :type2)
            order by abs(strcmp(:areaId1, pos.Area)) ASC,
            abs(strcmp(:cat1, pos.StkCat)) ASC,
            abs(strcmp(:type1, pos.SalesType)) ASC
            limit 1
        ";

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(
            CogsGLPosting::class, 'pos'
        );

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameters([
            'areaId1' => $area->getId(),
            'areaId2' => self::AREA_ANY,
            'cat1' => $cat->getId(),
            'cat2' => self::CATEGORY_ANY,
            'type1' => $type->getId(),
            'type2' => self::TYPE_ANY,
        ]);
        return $query->getSingleResult();
    }
}
