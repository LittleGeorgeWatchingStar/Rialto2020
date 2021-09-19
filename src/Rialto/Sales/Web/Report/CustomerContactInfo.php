<?php

namespace Rialto\Sales\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Time\Web\DateType;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Export all customer contact info.
 */
class CustomerContactInfo extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::SALES];
    }

    protected function getDefaultParameters(array $query): array
    {
        $thisYear = (int) date('Y');
        return [
            'startDate' => "$thisYear-01-01",
            'endDate' => date('Y-m-d'),
        ];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('startDate', DateType::class, [
                'input' => 'string',
                'label' => 'Customers with purchases made between',
            ])
            ->add('endDate', DateType::class, [
                'input' => 'string',
                'label' => 'and',
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }


    public function getTables(array $params): array
    {
        $tables = [];

        /* This query results in a huge dataset that can take a long time to
         * load. */
        ini_set('max_execution_time', 60);

        $sql = "
            select distinct
                if(c.CompanyName != '', c.CompanyName, b.BrName) as Company
                , if(c.Name != '', c.Name, b.ContactName) as Name
                , if(b.Email != '', b.Email, c.EDIAddress) as Email
                , b.PhoneNo as Phone
                , trim(concat_ws(' ', a.street1, a.street2, a.mailStop)) as Street
                , a.city as City
                , a.stateCode as State
                , a.postalCode as PostalCode
                , a.countryCode as Country
            from DebtorsMaster c
            join CustBranch b on c.DebtorNo = b.DebtorNo
            join Geography_Address a
                on c.addressID = a.id
            left join SalesOrders s on b.id = s.branchID
            where s.OrdDate >= :startDate
            and s.OrdDate <= :endDate
            having Email != ''
            order by Email
            ";

        $table = new RawSqlAudit('Customer contact info', $sql);

        $tables[] = $table;

        return $tables;
    }

}
