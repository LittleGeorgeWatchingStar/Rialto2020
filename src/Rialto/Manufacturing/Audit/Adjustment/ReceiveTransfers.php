<?php

namespace Rialto\Manufacturing\Audit\Adjustment;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Manufacturing\Audit\AuditItem;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Transfer\MissingTransferItem;
use Rialto\Stock\Transfer\Orm\TransferItemRepository;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferItem;
use Rialto\Stock\Transfer\TransferReceiver;
use Rialto\Stock\Transfer\Web\TransferReceipt;

/**
 * Automatically receives outstanding transfers when a CM tells us that
 * they have more stock than we thought.
 *
 * This strategy only handles upward adjustments.
 */
class ReceiveTransfers implements AdjustmentStrategy
{
    /** @var ObjectManager */
    private $om;

    /** @var TransferItemRepository */
    private $repo;

    /** @var TransferReceiver */
    private $receiver;

    /** @var AllocationFactory */
    private $factory;

    public function __construct(ObjectManager $om,
                                TransferReceiver $receiver,
                                AllocationFactory $factory)
    {
        $this->om = $om;
        $this->repo = $om->getRepository(TransferItem::class);
        $this->receiver = $receiver;
        $this->factory = $factory;
    }

    public function releaseFrom(AuditItem $item)
    {
        // does not apply
    }

    public function acquireFor(AuditItem $item)
    {
        $sources = [];
        foreach ($item->getAllocations() as $alloc) {
            if ($alloc->isInTransit()) {
                /** @var $bin StockBin */
                $bin = $alloc->getSource();
                $this->receiveBin($bin);
                $sources[] = $bin;
            }
        }
        $this->factory->allocate($item, $sources);
    }

    private function receiveBin(StockBin $bin)
    {
        $items = $this->findUnreceivedTransferItems($bin);
        foreach ($items as $transferItem) {
            if ($transferItem->isReceived()) {
                // $transferItem might have been received on a previous
                // iteration of this loop.
                continue;
            }
            $transfer = $transferItem->getTransfer();
            if ($transfer->isReceived()) {
                $this->receiveMissingItem($transferItem);
            } else {
                $this->receiveTransfer($transfer);
            }
        }
    }

    /**
     * @param AuditItem $item
     * @return TransferItem[]
     */
    private function findUnreceivedTransferItems(StockBin $bin)
    {
        return $this->repo->createBuilder()
            ->sent()
            ->unreceived()
            ->byBin($bin)
            ->getResult();
    }

    private function receiveTransfer(Transfer $transfer)
    {
        $receipt = new TransferReceipt($transfer);
        $this->receiver->receive($receipt);
    }

    private function receiveMissingItem(TransferItem $transferItem)
    {
        $receipt = new MissingTransferItem($transferItem);
        $receipt->setLocation(MissingTransferItem::LOCATION_DESTINATION);
        $this->receiver->resolveMissingItem($receipt);
    }

}
