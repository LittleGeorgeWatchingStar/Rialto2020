<?php

namespace Rialto\Web\Report;

use Doctrine\DBAL\Query\QueryBuilder;


/**
 * An audit table that is build using a DBAL query builder.
 */
class SqlQueryBuilderAudit
extends RawSqlAudit
{
    public function __construct($title, QueryBuilder $qb)
    {
        parent::__construct($title, $qb->getSQL());
    }
}
