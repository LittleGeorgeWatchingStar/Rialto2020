<?php

namespace Rialto\Database\Orm;

use Doctrine\ORM\Query;

/**
 * An entity repository that can build queries based on a set of
 * filter parameters.
 */
interface FilteringRepository
{
    /** @return Query */
    public function queryByFilters(array $params);
}
