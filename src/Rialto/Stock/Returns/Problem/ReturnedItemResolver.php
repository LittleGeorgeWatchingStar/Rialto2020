<?php

namespace Rialto\Stock\Returns\Problem;

use Psr\Log\LoggerInterface;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\Web\BinQtyType;
use Rialto\Stock\Count\StockAdjustment;
use Rialto\Stock\Move\Orm\StockMoveRepository;
use Rialto\Stock\Move\StockMove;
use Rialto\Stock\Returns\ReturnedItem;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferReceiver;
use Rialto\Stock\Transfer\Web\TransferReceipt;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Resolves bins which have been returned to us but cannot be checked in
 * for some reason.
 */
class ReturnedItemResolver
{
    /** @var DbManager */
    private $dbm;

    /** @var TransferReceiver */
    private $receiver;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DbManager $dbm,
        TransferReceiver $receiver,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger)
    {
        $this->dbm = $dbm;
        $this->receiver = $receiver;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * If the bin is not known, look for bins that it might be.
     *
     * @return StockBin[]
     */
    public function getPossibleBins(ReturnedItem $item)
    {
        if ($item->hasBin()) {
            return [$item->getBin()];
        }

        /** @var $repo StockBinRepository */
        $repo = $this->dbm->getRepository(StockBin::class);
        $builder = $repo->createBuilder()
            ->atFacility($item->getReturnedFrom());

        if ($item->hasItem()) {
            $builder->byItem($item);
        } elseif (($mpn = $item->getManufacturerCode())) {
            $builder->byManufacturerCode($mpn);
        } else {
            return [];
        }
        return $builder->getResult();
    }

    /** @return StockMove[] */
    public function findStockMoves(ReturnedItem $item)
    {
        if (!$item->hasBin()) {
            return [];
        }
        /** @var StockMoveRepository $repo */
        $repo = $this->dbm->getRepository(StockMove::class);
        return $repo->findByBin($item->getBin());
    }

    /**
     * A description the resolutions for the item, if any exist.
     *
     * @see resolveItem()
     */
    public function populateResolution(ItemResolution $resolution, FormBuilderInterface $builder)
    {
        $item = $resolution->getItem();
        if (($transfer = $resolution->getOpenTransferToCM())) {
            $builder->add('resolve', SubmitType::class, [
                'label' => "Receive $transfer."
            ]);
            $resolution->setForm($builder->getForm());
        } elseif ($item->hasQtyMismatch()) {
            $resolution->setOtherBins($this->loadOtherBins($item));
            $builder
                ->add('otherBins', CollectionType::class, [
                    'entry_type' => BinQtyType::class,
                    'label' => 'returned_item.otherBins',
                ])
                ->add('resolve', SubmitType::class, [
                    'label' => 'Adjust stock',
                ]);
            $resolution->setForm($builder->getForm());
        }
    }

    /** @return StockBin[] */
    private function loadOtherBins(ReturnedItem $item)
    {
        /** @var $repo StockBinRepository */
        $repo = $this->dbm->getRepository(StockBin::class);
        return $repo->createBuilder()
            ->available()
            ->atFacility($item->getReturnedFrom())
            ->byVersionedItem($item->getBin())
            ->excludeBin($item->getBin())
            ->getResult();
    }

    /**
     * Resolves any problems with the item that can be automatically resolved.
     *
     * @see populateResolution()
     */
    public function resolveItem(ItemResolution $resolution)
    {
        $item = $resolution->getItem();
        if (($transfer = $resolution->getOpenTransferToCM())) {
            $this->receiveItem($transfer, $item->getBin());
            $this->adjustBinQuantity($item, $resolution->getOtherBins());
        }
    }

    /**
     * The obvious (but rather brute-force) way to resolve a quantity
     * mismatch is to just do the stock adjustment.
     *
     * @param StockBin[] $otherBins
     */
    private function adjustBinQuantity(ReturnedItem $item, array $otherBins)
    {
        $bin = $item->getBin();
        $bin->setNewQty($item->getQuantity());
        $memo = "Resolved returned $bin by stock adjustment";
        $adjustment = new StockAdjustment($memo);
        $adjustment->setEventDispatcher($this->dispatcher);
        $adjustment->addBin($bin);
        foreach ($otherBins as $otherBin) {
            $adjustment->addBin($otherBin);
        }
        $transaction = $adjustment->adjust($this->dbm);
        $this->dbm->persist($transaction);

        $this->logger->notice($memo);
    }

    private function receiveItem(Transfer $transfer, StockBin $bin)
    {
        $receipt = new TransferReceipt($transfer);
        $receipt->addItem($transfer->getItem($bin));
        $this->receiver->receive($receipt);

        $this->logger->notice("Resolved returned $bin by receiving $transfer");
    }
}
