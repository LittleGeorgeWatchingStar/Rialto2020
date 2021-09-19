<?php

namespace Rialto\Stock\Transfer;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Stock\Bin\Event\BinQuantityChanged;
use Rialto\Stock\Transfer\Orm\TransferItemRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener service for any bin events that may require a compensation action
 * to be performed on a transfer.
 */
final class BinEventListener implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var TransferItemRepository */
    private $transferItemRepo;

    /**
     * BinEventListener constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->transferItemRepo = $em->getRepository(TransferItem::class);
    }

    public static function getSubscribedEvents()
    {
        return [
            BinQuantityChanged::class => 'onBinQtyChanged',
        ];
    }

    /**
     * Cascade a bin quantity change to any transfers that may have their
     * own records of how much stock was included on an order.
     */
    public function onBinQtyChanged(BinQuantityChanged $event): void
    {
        $bin = $event->getBin();
        $newQty = $event->getNewQty();

        /** @var TransferItem[] */
        $items = $this->transferItemRepo->findBy([
            'stockBin' => $bin,
        ]);

        foreach ($items as $item) {
            $item->updateQtySent($newQty);
        }

        $this->em->flush();
    }
}
