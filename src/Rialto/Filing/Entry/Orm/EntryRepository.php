<?php

namespace Rialto\Filing\Entry\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;

class EntryRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('entry');
        $builder->add('document', function($qb, $documentID) {
            $qb->andWhere('entry.document = :documentID')
                ->setParameter('documentID', $documentID);
        });
        return $builder->buildQuery($params);
    }
}
