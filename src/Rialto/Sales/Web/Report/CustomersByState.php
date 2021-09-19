<?php

namespace Rialto\Sales\Web\Report;

use Rialto\Cms\CmsEntry;
use Rialto\Sales\Customer\Web\CustomerAuditFilterType;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Security\Role\Role;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\DqlAudit;
use Symfony\Component\Form\FormBuilderInterface;

class CustomersByState extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::SALES];
    }

    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        $tables = [];

        $qb = $this->dbm->createQueryBuilder();
        $qb->select([
            'c.id as CustomerID',
            'c.companyName as Company',
            'c.name as Name',
            'c.email as Email',
            "concat(a.street1, ', ', a.street2, if(a.street2 = '', '', ', '), a.city, ', ', a.stateCode, ' ', a.postalCode, ', ', a.countryCode) as Address",
            'max(o.dateOrdered) as LastOrderDate',
        ])
            ->from(SalesOrder::class, 'o')
            ->join('o.customerBranch', 'b')
            ->join('b.customer', 'c')
            ->join('c.address', 'a')
            ->groupBy('c.id')
            ->orderBy('c.companyName')
            ->addOrderBy('c.name')
        ;

        if (! empty($params['startDate']) ) {
            $qb->andHaving('LastOrderDate >= :startDate');
        }
        if (! empty($params['endDate']) ) {
            $qb->andHaving('LastOrderDate <= :endDate');
        }
        if (! empty($params['country']) ) {
            $qb->andWhere('a.countryCode = :country');
        }
        if (! empty($params['state']) ) {
            $qb->andWhere('a.stateCode in (:state)');
        }
        if (! empty($params['salesman']) ) {
            $qb->andWhere('b.salesman = :salesman');
        }

        $table = new DqlAudit("Customers", $qb->getDQL());
        $table->setDescription($this->getConfidentialityStatement());

        $tables[] = $table;

        return $tables;
    }

    private function getConfidentialityStatement()
    {
        $cmsEntry = CmsEntry::fetch('sales.confidentiality', $this->dbm);
        return $cmsEntry ? $cmsEntry->getContent() : '';
    }


    /**
     * @return string[]
     */
    protected function getDefaultParameters(array $query): array
    {
        $thisYear = date('Y');
        return [
            'startDate' => "$thisYear-01-01",
            'country' => 'US',
        ];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder->getFormFactory()->create(CustomerAuditFilterType::class);
    }

}
