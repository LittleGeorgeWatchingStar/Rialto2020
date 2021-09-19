<?php

namespace Rialto\Manufacturing\Audit\Adjustment;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Manufacturing\Audit\AuditItem;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Producer\Orm\StockProducerRepository;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Rialto\Purchasing\Receiving\Receiver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Automatically receives outstanding purchase orders when a CM tells us that
 * they have more stock than we thought.
 *
 * This strategy only handles upward adjustments.
 */
class ReceivePurchaseOrders implements AdjustmentStrategy
{
    /** @var ObjectManager */
    private $om;

    /** @var StockProducerRepository */
    private $repo;

    /** @var Receiver */
    private $receiver;

    /** @var TokenStorageInterface */
    private $tokens;

    /**
     * Whether we should receive the whole PO item or just the qty that
     * we need right now.
     */
    const RECEIVE_ENTIRE_ITEM = true;  // TODO: php7.1 make private

    public function __construct(ObjectManager $om,
                                Receiver $receiver,
                                TokenStorageInterface $tokens)
    {
        $this->om = $om;
        $this->repo = $om->getRepository(PurchaseOrderItem::class);
        $this->receiver = $receiver;
        $this->tokens = $tokens;
    }

    public function releaseFrom(AuditItem $item)
    {
        // does not apply
    }

    public function acquireFor(AuditItem $item)
    {
        foreach ($item->getAllocations() as $alloc) {
            if ($alloc->isDelivered()) {
                continue;
            }
            if ($alloc->isOnOrderTo($item->getBuildLocation())) {
                /** @var $orderItem StockProducer */
                $orderItem = $alloc->getSource();
                if ($orderItem->canBeReceived()) {
                    $toReceive = $this->getQtyToReceive($item, $orderItem);
                    $this->receiveItem($orderItem, $toReceive);
                }
            }
        }
    }

    private function getQtyToReceive(AuditItem $auditItem, StockProducer $orderItem)
    {
        if (self::RECEIVE_ENTIRE_ITEM) {
            return $orderItem->getQtyRemaining();
        } else {
            $status = $auditItem->getAllocationStatus();
            return $auditItem->getActualQty() - $status->getQtyAtLocation();
        }
    }

    private function receiveItem(StockProducer $orderItem, $qtyToReceive)
    {
        $po = $orderItem->getPurchaseOrder();
        $grn = new GoodsReceivedNotice($po, $this->getCurrentUser());
        $qtyToReceive = min($qtyToReceive, $orderItem->getQtyRemaining());
        $grn->addItem($orderItem, $qtyToReceive);
        $this->om->persist($grn);
        $this->om->flush();
        $this->receiver->receive($grn);
    }

    private function getCurrentUser()
    {
        return $this->tokens->getToken()->getUser();
    }
}
