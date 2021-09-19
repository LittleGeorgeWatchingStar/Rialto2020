<?php

namespace Rialto\Stock\Web\Report;


use Rialto\Accounting\Transaction\SystemType;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Shows how much stock was used for building and selling stuff over
 * the given time period.
 */
class StockConsumption extends BasicAuditReport
{
    const DEFAULT_SINCE = '-42 days';

    protected function getDefaultParameters(array $query): array
    {
        return [
            'since' => (new \DateTime(self::DEFAULT_SINCE))->format('Y-m-d'),
            'types' => [
                SystemType::SALES_INVOICE,
                SystemType::WORK_ORDER_ISSUE,
                // avoid double-counting issues that were reversed
                SystemType::WORK_ORDER_ISSUE_REVERSAL,
            ],
        ];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('since', DateType::class, [
                'input' => 'string',
                'widget' => 'single_text',
            ])
            ->add('sku', SearchType::class, [
                'required' => false,
                'label' => 'SKU match',
                'attr' => [
                    'placeholder' => 'substring...'
                ],
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }

    public function prepareParameters(array $params): array
    {
        $sku = $params['sku'] ?? '';
        $params['sku'] = "%$sku%";
        return $params;
    }

    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        $tables = [];

        $numDays = (new \DateTime())->diff(new \DateTime($params['since']))->days;
        $inStock = $this->inStockSubquery();
        $alloc = $this->allocSubquery();
        $purch = $this->purchDataSubquery();
        $onOrder = $this->onOrderSubquery();
        $orderPoint = $this->orderPointSubquery();

        $table = new RawSqlAudit("Stock consumption", "
            select item.StockID as sku
            , bin.Version as version
            , ifnull(supplier.SuppName, 'choose preferred→') as supplier
            , sum(-move.quantity) as qty
            , sum(-move.quantity) / $numDays as daily
            , ifnull(InStock.total, 0) - ifnull(Alloc.total, 0) as inStock
            , (ifnull(InStock.total, 0) - ifnull(Alloc.total, 0)) / (sum(-move.quantity) / $numDays) as remaining
            , Purch.maxLead as lead
            , ifnull(OnOrder.total, 0) as onOrder
            , stdCost.materialCost + stdCost.labourCost + stdCost.overheadCost as stdCost
            , sum(-move.quantity) / $numDays * Purch.maxLead as op
            , ifnull(OP.orderPoint, 'init→') as actual
            from StockMove as move
            join StockSerialItems as bin
                on move.binID = bin.SerialNo
            join StockMaster as item
                on bin.StockID = item.StockID
            join Accounting_Transaction as trans
                on move.transactionId = trans.id
            left join ( $inStock ) as InStock
                on InStock.sku = item.StockID
                and InStock.version = bin.Version
            left join ( $alloc ) as Alloc
                on Alloc.sku = item.StockID
                and Alloc.version = bin.Version
            left join ( $purch ) as Purch
                on Purch.sku = item.StockID
                and Purch.version in ('-any-', bin.Version)
            left join Suppliers supplier
                on Purch.supplier = supplier.SupplierID
            left join ( $onOrder ) as OnOrder
                on OnOrder.sku = item.StockID
                and OnOrder.version = bin.Version
            left join StandardCost stdCost
                on item.currentStandardCost = stdCost.id
            left join ( $orderPoint ) as OP
                on OP.sku = item.StockID
            where trans.sysType in (:types)
            and item.MBflag = 'B'
            and item.StockID like :sku
            and move.dateMoved >= :since
            and bin.customizationId is null
            group by item.StockID, bin.Version
        ");
        $table->setDescription(sprintf(
            "Stock consumption since %s (%d days ago)",
            $params['since'],
            $numDays));

        $table->setLink('qty', 'stock_move_list', function (array $row) use ($params) {
            return [
                'item' => $row['sku'],
                'startDate' => $params['since'],
            ];
        });
        $table->setLink('sku', 'stock_item_view', function (array $row) {
            return ['item' => $row['sku']];
        });
        $table->setLink('supplier', 'purchasing_data_list', function (array $row) {
            return ['stockItem' => $row['sku']];
        });

        $table->setScale('inStock', 0);
        $table->setLink('inStock', 'Stock_StockLevel_list', function (array $row) {
            return ['stockCode' => $row['sku']];
        });
        $table->setScale('daily', 2);
        $table->setScale('remaining', 0);
        $table->setScale('lead', 0);
        $table->setLink('lead', 'Purchasing_LeadTime', function (array $row) {
            return ['stockItem' => $row['sku']];
        });
        $table->setScale('onOrder', 0);
        $table->setLink('onOrder', 'purchase_order_list', function (array $row) {
            return [
                'stockItem' => $row['sku'],
                'completed' => 'no',
            ];
        });
        $table->setScale('stdCost', 4);
        $table->setLink('stdCost', 'item_standard_cost', function (array $row) {
            return ['item' => $row['sku']];
        });
        $table->setScale('op', 0);
        $table->setScale('actual', 0);
        $table->setLink('actual', 'Stock_StockLevel_list', function (array $row) {
            return ['stockCode' => $row['sku']];
        });

        $tables[] = $table;
        return $tables;
    }

    private function inStockSubquery(): string
    {
        return "
            select bin.StockID as sku
            , bin.Version as version
            , sum(bin.Quantity) as total
            from StockSerialItems as bin
            where bin.customizationId is null
            group by bin.StockID, bin.Version
        ";
    }

    private function allocSubquery(): string
    {
        return "
            select bin.StockID as sku
            , bin.Version as version
            , sum(alloc.Qty) as total
            from StockAllocation as alloc
            join StockSerialItems as bin
                on alloc.SourceNo = bin.SerialNo
            where alloc.SourceType = 'StockBin'
            and bin.customizationId is null
            group by bin.StockID, bin.Version
        ";
    }

    private function purchDataSubquery(): string
    {
        return "
            select purch.StockID as sku
            , purch.Version as version
            , purch.SupplierNo as supplier
            , min(costBreak.manufacturerLeadTime) as minLead
            , max(costBreak.manufacturerLeadTime) as maxLead
            from PurchData as purch
            join PurchasingCost costBreak
                on purch.ID = costBreak.purchasingDataId
            where purch.Preferred = 1
            group by purch.StockID, purch.Version
        ";
    }

    private function onOrderSubquery(): string
    {
        return "
            select sum(greatest(item.qtyOrdered - item.qtyReceived, 0)) as total
            , purch.StockID as sku
            , item.Version as version
            from StockProducer item
            join PurchData purch
                on item.purchasingDataID = purch.ID
            where item.type = 'parts'
            and item.dateClosed is null
            group by purch.StockID, item.Version
        ";
    }

    private function orderPointSubquery(): string
    {
        return "
            select stockCode as sku
            , sum(orderPoint) as orderPoint
            from StockLevelStatus
            group by stockCode
        ";
    }
}
