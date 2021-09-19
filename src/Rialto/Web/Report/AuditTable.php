<?php

namespace Rialto\Web\Report;

use Rialto\Database\Orm\DoctrineDbManager;

/**
 * A table in an AuditReport.
 */
interface AuditTable
{
    public function getKey();

    public function getTitle();

    public function getDescription();

    public function loadResults(DoctrineDbManager $dbm, array $params);

    /** @return AuditColumn[] */
    public function getColumns();

    public function getResults();

    public function getTotal();
}
