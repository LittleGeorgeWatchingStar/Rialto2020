<?php

namespace Rialto\Sales\Web\Report;

use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Cms\CmsEntry;
use Rialto\Sales\Customer\Web\CustomerAuditFilterType;
use Rialto\Security\Role\Role;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\DqlAudit;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Report of orders and order revenue, filterable by state, used for
 * calculating sales rep commissions.
 */
class Commission extends BasicAuditReport
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
            'c.companyName as Company',
            'c.name as Name',
            'o.contactEmail as Email',
            "concat(sa.street1, ', ', sa.street2, if(sa.street2 = '', '', ', '), sa.city, ', ', sa.stateCode, ' ', sa.postalCode, ', ', sa.countryCode) as ShippingAddress",
            'o.id as OrderNo',
            'sum(inv.subtotalAmount + inv.discountAmount) as NetRevenue',
        ])
            ->from(DebtorInvoice::class, 'inv')
            ->join('inv.salesOrder', 'o')
            ->join('o.shippingAddress', 'sa')
            ->join('inv.customer', 'c')
            ->join('o.customerBranch', 'b')
            ->groupBy('o.id')
            ->having('NetRevenue > 0')
            ->orderBy('c.companyName')
            ->addOrderBy('o.id')
        ;

        if (! empty($params['startDate']) ) {
            $qb->andWhere('inv.date >= :startDate');
        }
        if (! empty($params['endDate']) ) {
            $qb->andWhere('inv.date <= :endDate');
        }
        if (! empty($params['country']) ) {
            $qb->andWhere('sa.countryCode = :country');
        }
        if (! empty($params['state']) ) {
            $qb->andWhere('sa.stateCode in (:state)');
        }
        if (! empty($params['salesman']) ) {
            $qb->andWhere('b.salesman = :salesman');
        }

        $table = new DqlAudit("Sales revenue", $qb->getDQL());
        $table->setDescription($this->getConfidentialityStatement());
        $table->setScale('NetRevenue', 2);

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
            'country' => null,
        ];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder->getFormFactory()->create(CustomerAuditFilterType::class);
    }
}
