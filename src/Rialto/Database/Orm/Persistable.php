<?php

namespace Rialto\Database\Orm;

/**
 * Something that can be persisted via the db manager's persist() method.
 */
interface Persistable
{
    public function getEntities();
}
