<?php

namespace Rialto\Entity\Orm;

use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Entity\EntityAttribute;


/**
 * Base class for EntityAttribute repositories.
 */
abstract class EntityAttributeRepository extends RialtoRepositoryAbstract
{
    /**
     * @return EntityAttribute[]
     */
    public abstract function findByEntity($stockItem);
}
