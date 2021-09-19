<?php

namespace Rialto\Manufacturing\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Web\ActiveFacilityType;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class GeppettoItemVelocity extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::EMPLOYEE];
    }

    public function getTables(array $params): array
    {
        $tables = [];

        $sql = $this->getQuery(true);
        $table = new RawSqlAudit('Geppetto Reel Velocity', $sql);
        $table->setScale('Cumulative', 0);

        $tables[] = $table;

        $sql = $this->getQuery(false);
        $table = new RawSqlAudit('Geppetto Non-Reel Velocity', $sql);
        $table->setScale('Cumulative', 0);

        $tables[] = $table;

        return $tables;
    }

    private function getQuery(bool $reels): string
    {
        $operator = $reels ? 'LIKE' : 'NOT LIKE';
        return "SELECT issueItem.StockID, componentPurch.BinStyle, sum(issueItem.unitQtyIssued * issue.QtyIssued) AS Cumulative
            FROM WOIssueItems issueItem
            JOIN WOIssues issue ON issueItem.IssueID = issue.IssueNo 
            JOIN StockProducer workOrder ON issue.WorkOrderID=workOrder.id 
            JOIN PurchData parentPurch ON workOrder.purchasingDataID=parentPurch.id 
            JOIN PurchData componentPurch ON componentPurch.StockID = issueItem.StockID
            JOIN StockMaster component ON component.StockID = issueItem.StockID
            WHERE parentPurch.StockID  LIKE 'BRD900%'
            AND  parentPurch.LocCode LIKE :facility
            AND  componentPurch.StockID LIKE :sku
            AND  componentPurch.preferred = 1
            AND  componentPurch.BinStyle $operator 'reel%'
            AND  component.CategoryID = :partCategory
            AND  issue.IssueDate >= :startDate
            GROUP BY issueItem.StockID";
    }

    protected function getDefaultParameters(array $query): array
    {
        return [
            'startDate' => date('Y-m-d', strtotime('-1 year')),
            'facility' => null,
            'sku' => null,
            'partCategory' => StockCategory::PART,
        ];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('sku', SearchType::class, [
                'required' => false,
                'label' => "SKU",
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Since',
                'required' => false,
                'input' => 'string',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('facility', ActiveFacilityType::class, [
                'required' => false,
                'placeholder' => '-- all --',
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }

    public function prepareParameters(array $params): array
    {
        $params['sku'] = "%" . $params['sku'] . "%";
        $params['facility'] = $params['facility'] ? $params['facility']->getId() : '%';
        return $params;
    }


}
