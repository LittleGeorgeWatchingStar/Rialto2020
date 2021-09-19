<?php

namespace Rialto\Manufacturing\PurchaseOrder;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\WorkOrder\Orm\WorkOrderRepository;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderItem;


/**
 * Adds line items for manufacturing expenses to a PO.
 *
 * Manufacturing expenses are things like the cost of creating stencils and
 * programming for the SMT machine.
 */
class ManufacturingExpense
{
    /** @var DbManager */
    private $dbm;

    /** @var WorkOrderRepository */
    private $woRepo;

    /** @var PurchasingDataRepository */
    private $pdRepo;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
        $this->woRepo = $this->dbm->getRepository(WorkOrder::class);
        $this->pdRepo = $this->dbm->getRepository(PurchasingData::class);
    }

    public function addManufacturingExpensesIfNeeded(PurchaseOrder $po)
    {
        if (! $po->hasSupplier()) {
            return;
        }

        $minimumLot = $this->pdRepo->findMinimumLotCharge($po->getSupplier());
        if ($minimumLot) {
            if (count ($po->getWorkOrders()) > 0) {
                $minimumLotCost = $minimumLot->getCost();
                $totalManufacturedCost = 0;
                foreach ($po->getWorkOrders() as $workOrder) {
                    /** @var WorkOrder $workOrder */
                    $manufacturedCost = $workOrder->getExtendedCost();
                    $totalManufacturedCost += $manufacturedCost;
                }

                $orders = $po->getWorkOrders();
                $firstWorkOrder = array_pop($orders);
                if ($totalManufacturedCost < $minimumLotCost) {
                    $this->addManufacturingExpense($po, $firstWorkOrder, $minimumLot,
                        $minimumLotCost - $totalManufacturedCost);
                }
            }
        }

        $expenses = $this->pdRepo->findManufacturingExpenses($po->getSupplier());

        foreach ($expenses as $purchData) {
            foreach ($po->getWorkOrders() as $wo) {
                if ($this->woRepo->needsAdditionalLineItem($wo, $purchData)) {
                    $this->addManufacturingExpense($po, $wo, $purchData);
                }
            }
        }
    }

    private function addManufacturingExpense(PurchaseOrder $po,
                                             WorkOrder $wo,
                                             PurchasingData $data,
                                             ?float $cost = null)
    {
        $account = GLAccount::fetchDevelopmentExpense($this->dbm);
        $poItem = $po->addNonStockItem($account);
        $poItem->setDescription(sprintf('%s %s',
            ucfirst(strtolower($data->getSku())),
            $wo->getSku()
        ));

        if ($cost === null) {
            $cost = $data->getCost();
        }
        $flags = [
            PurchaseOrderItem::FLAG_AUTO_RECEIVE,
        ];
        if ($cost == 0) {
            $flags[] = PurchaseOrderItem::FLAG_ZERO_COST;
        }
        $poItem->setUnitCost($cost);
        $poItem->setFlags($flags);
        $poItem->setQtyOrdered(1);
        $poItem->setRequestedDate($wo->getRequestedDate());
    }
}
