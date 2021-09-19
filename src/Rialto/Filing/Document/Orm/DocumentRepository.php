<?php

namespace Rialto\Filing\Document\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Filing\Document\Document;

class DocumentRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('doc');
        // no filters needed yet
        return $builder->buildQuery($params);
    }

    /**
     * All recurring documents.
     * @return Document[]
     */
    public function findRecurring()
    {
        $qb = $this->createQueryBuilder('doc');
        $qb->where('doc.scheduleDay != 0')
            ->andWhere("doc.scheduleMonths != ''");
        return $qb->getQuery()->getResult();
    }
}
