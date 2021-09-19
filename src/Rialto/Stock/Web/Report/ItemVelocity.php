<?php

namespace Rialto\Stock\Web\Report;

use Gumstix\Time\DateRange;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Shelf\Velocity\VelocityQueryBuilder;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\DqlAudit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ItemVelocity extends BasicAuditReport
{
    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        $tables = [];

        $dates = DateRange::create()
            ->withStart($params['startDate']);

        $qb = new VelocityQueryBuilder($this->dbm);
        $qb->byDates($dates)
            ->orderByDateMoved();
        if ($params['facility']) {
            $qb->byFacility($params['facility']);
        }
        if ($params['sku']) {
            $qb->byItem($params['sku']);
        }
        $table = new DqlAudit("Stock item velocity", $qb->getDQL());
        $tables[] = $table;

        return $tables;
    }

    protected function getDefaultParameters(array $query): array
    {
        return [
            'startDate' => date('Y-m-d', strtotime('-1 year')),
            'facility' => null,
            'sku' => null,
        ];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('facility', EntityType::class, [
                'class' => Facility::class,
                'required' => false,
                'placeholder' => '-- any --',
            ])
            ->add('sku', TextType::class, [
                'required' => false,
                'label' => "SKU",
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Since',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'string',
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }

    public function getAllowedRoles()
    {
        return [Role::EMPLOYEE];
    }
}
