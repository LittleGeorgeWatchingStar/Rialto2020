<?php

namespace Rialto\Purchasing\Order;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\WorkOrder\WorkOrderCreation;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Creates new PurchaseOrder objects.
 */
class PurchaseOrderFactory
{
    private $dbm;
    private $poOwner = null;

    public function __construct(DbManager $dbm, string $defaultOwner)
    {
        $this->dbm = $dbm;
        $this->poOwner = $dbm->need(User::class, $defaultOwner);
        assertion(null !== $this->poOwner);
    }

    private function getDefaultLocation()
    {
        return Facility::fetchHeadquarters($this->dbm);
    }

    public function loadCurrentUser(TokenStorageInterface $storage)
    {
        $token = $storage->getToken();
        $user = $token ? $token->getUser() : null;
        $this->poOwner = $user ?: $this->poOwner;
    }

    public function create(PurchaseInitiator $initiator): PurchaseOrder
    {
        $po = new PurchaseOrder($initiator->getInitiatorCode(), $this->poOwner);
        $po->setDeliveryLocation($this->getDefaultLocation());
        return $po;
    }

    public function forSingleItem(SingleItemPurchaseOrder $sipo): PurchaseOrder
    {
        $po = $this->create($sipo);
        $purchData = $sipo->getPurchasingData();
        $supplier = $purchData->getSupplier();
        $po->setSupplier($supplier);

        $poItem = $po->addItemFromPurchasingData($purchData);
        $poItem->setQtyOrdered($sipo->getOrderQty());
        $poItem->setVersion($sipo->getVersion());
        $poItem->resetUnitCost();
        $poItem->roundQtyOrdered();

        return $po;
    }

    public function forSingleItemManual(SingleItemManualPurchaseOrder $sipo): StockProducer
    {
        $po = $this->create($sipo);
        $purchData = $sipo->getPurchasingData();
        $supplier = $purchData->getSupplier();
        $po->setSupplier($supplier);

        $poItem = $po->addItemFromPurchasingData($purchData);
        $poItem->setQtyOrdered($sipo->getOrderQty());
        $poItem->setVersion($sipo->getVersion());
        $poItem->resetUnitCost();
        $poItem->roundQtyOrdered();

        return $poItem;
    }

    /**
     * Creates a PO for the given work order, if one is needed.
     */
    public function forWorkOrder(WorkOrderCreation $creation): PurchaseOrder
    {
        $location = $creation->getLocation();
        $po = PurchaseOrder::fromLocation($location, $creation, $this->poOwner);
        $po->setDeliveryLocation($this->getDefaultLocation());
        $this->dbm->persist($po);
        return $po;
    }
}
