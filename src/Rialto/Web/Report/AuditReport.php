<?php

namespace Rialto\Web\Report;

use Rialto\Database\Orm\DbManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * A collection of AuditTables that generates a report for auditing
 * purposes.
 *
 * To write a new audit report, implement this interface (typically by extending
 * BasicAuditReport) and put the file in {module}/Web/Report/.
 */
interface AuditReport
{
    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array;

    public function init(DbManager $dbm, array $params);

    public function getParameters(array $params): array;

    /**
     * Final preparation of parameters (both default and query string) before
     * the queries are constructed and executed.
     *
     * Good for, eg, wrapping parameters in SQL wildcards.
     *
     * @return string[]
     */
    public function prepareParameters(array $params): array;

    /** @return FormInterface|null */
    public function getFilterForm(FormBuilderInterface $builder);

    /**
     * The roles that are allowed to view this report.
     * @return string[]
     */
    public function getAllowedRoles();
}
