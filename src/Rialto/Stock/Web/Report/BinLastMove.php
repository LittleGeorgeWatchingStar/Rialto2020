<?php

namespace Rialto\Stock\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Move\StockMove;
use Rialto\Time\Web\DateType;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\DqlAudit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BinLastMove extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::STOCK];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('startDate', DateType::class, [
                'input' => 'string',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'input' => 'string',
                'required' => false,
            ])
            ->add('stockCode', TextType::class, [
                'required' => false,
            ])
            ->add('location', EntityType::class, [
                'class' => Facility::class,
                'required' => false,
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }

    /**
     * @return string[]
     */
    protected function getDefaultParameters(array $query): array
    {
        $lastYear = date('Y') - 2;
        return [
            'endDate' => "$lastYear-12-31",
        ];
    }

    public function prepareParameters(array $params): array
    {
        $stockCode = isset($params['stockCode']) ? $params['stockCode'] : '';
        $params['stockCode'] = "%$stockCode%";
        return $params;
    }


    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        $tables = [];

        $title = "Stock bins' last moves";
        $qb = $this->dbm->createQueryBuilder();
        $qb->select([
            'item.stockCode',
            'bin.id as binId',
            'move.date lastMoveDate',
            "ifnull(facility.name, concat('transfer ', transfer.id)) as moveLocation",
            'type.name as moveType',
            'move.reference as memo',
            'move.quantity as moveQty',
            'bin.quantity as qtyLeft',
            'bin.quantity * (item.materialCost + item.labourCost + item.overheadCost) as value',
        ])
            ->from(StockBin::class, 'bin')
            ->join('bin.stockItem', 'item')
            // How to find the most recent stock move:
            ->join(StockMove::class, 'move', 'WITH',
                'move.stockBin = bin')
            // - It has no subsequent moves
            ->leftJoin(StockMove::class, 'nextMove', 'WITH',
                'nextMove.stockBin = bin and nextMove.date > move.date')
            ->andWhere('nextMove.id is null')
            // - It is where the bin currently resides
            ->andWhere('bin.facility = move.facility or bin.transfer = move.transfer')

            ->leftJoin('move.facility', 'facility')
            ->leftJoin('move.transfer', 'transfer')
            ->leftJoin('move.systemType', 'type')

            ->andWhere('bin.quantity > 0')
            ->andWhere('item.stockCode like :stockCode')
            ->orderBy('item.stockCode')
            ->addOrderBy('bin.id');

        if (!empty($params['location'])) {
            $qb->andWhere('facility = :location');
        }
        if (!empty($params['startDate'])) {
            $qb->andWhere('move.date >= :startDate');
        }
        if (!empty($params['endDate'])) {
            $qb->andWhere('move.date <= :endDate');
        }

        $table = new DqlAudit($title, $qb->getDQL());
        $table->setScale('moveQty', 0);
        $table->setScale('quantity', 0);
        $table->setScale('value', 2);

        $tables[] = $table;
        return $tables;
    }

}
