<?php

namespace Rialto\Database\Orm;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

/**
 * DbManager is Rialto's extension and wrapper around Doctrine's
 * ObjectManager.
 *
 * It exists largely for historical reasons: Rialto originally did not
 * use Doctrine, so this interface was used to make the transition.
 */
interface DbManager extends ObjectManager
{
    /**
     * @throws DbKeyException
     *  If an entity with the given id is not found.
     */
    public function need($className, $id);

    /**
     * @deprecated Use persist() and flush() instead.
     */
    public function save($className,  $model);

    /**
     * @deprecated Use remove() and flush() instead
     */
    public function delete($className,  $model);

    /** @return QueryBuilder */
    public function createQueryBuilder();

    public function beginTransaction();

    public function flushAndCommit();

    public function rollBack();
}
