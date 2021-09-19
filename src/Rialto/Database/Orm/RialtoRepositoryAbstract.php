<?php

namespace Rialto\Database\Orm;

use Doctrine\ORM\EntityRepository;

abstract class RialtoRepositoryAbstract extends EntityRepository
{
    public function need($id)
    {
        $entity = $this->find($id);
        if ( $entity ) {
            return $entity;
        }
        throw new DbKeyException(
            $this->_class->getTableName(),
            $id
        );
    }

    protected function mixedToPure(array $results)
    {
        $pure = [];
        foreach ( $results as $result ) {
            $pure[] = $result[0];
        }
        return $pure;
    }
}
