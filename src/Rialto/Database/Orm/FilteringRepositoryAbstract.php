<?php

namespace Rialto\Database\Orm;

abstract class FilteringRepositoryAbstract
extends RialtoRepositoryAbstract
implements FilteringRepository
{
    /** @return FilterQueryBuilder */
    protected function createRestBuilder($alias)
    {
        return new FilterQueryBuilder($this->createQueryBuilder($alias));
    }
}
