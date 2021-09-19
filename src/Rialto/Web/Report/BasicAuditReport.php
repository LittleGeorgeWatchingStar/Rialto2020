<?php

namespace Rialto\Web\Report;

use Rialto\Database\Orm\DbManager;
use Rialto\Security\Role\Role;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * An AuditReport with no initialization.
 */
abstract class BasicAuditReport implements AuditReport
{
    /** @var DbManager */
    protected $dbm;

    public function init(DbManager $dbm, array $params)
    {
        $this->dbm = $dbm;
    }

    public function getParameters(array $params): array
    {
        return array_merge($this->getDefaultParameters($params), $params);
    }

    /**
     * @override as needed
     */
    protected function getDefaultParameters(array $params): array
    {
        return $params;
    }

    /**
     * @override as needed
     */
    public function prepareParameters(array $params): array
    {
        return $params;
    }

    /** @return FormInterface|null */
    public function getFilterForm(FormBuilderInterface $builder)
    {
        return null;
    }

    public function getAllowedRoles()
    {
        /* Restrict access by default. */
        return [Role::ADMIN];
    }
}
