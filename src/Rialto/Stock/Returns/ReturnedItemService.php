<?php

namespace Rialto\Stock\Returns;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Returns\Problem\ReturnedItemResolver;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferService;

class ReturnedItemService
{
    /** @var ObjectManager */
    private $om;

    /** @var ReturnedItemRepository */
    private $repo;

    /** @var ReturnedItemResolver */
    private $resolver;

    /** @var TransferService */
    private $transferSvc;

    public function __construct(ObjectManager $om,
                                ReturnedItemResolver $resolver,
                                TransferService $transferSvc)
    {
        $this->om = $om;
        $this->repo = $om->getRepository(ReturnedItem::class);
        $this->resolver = $resolver;
        $this->transferSvc = $transferSvc;
    }

    public function handleProblems(ReturnedItems $items)
    {
        $items->resolveSmallProblems($this->resolver);
        $problems = $items->persistItemsWithProblems($this->om);
        return $problems;
    }

    /**
     * @return Transfer|null Null if all items have problems.
     */
    public function transferItemsWithoutProblems(ReturnedItems $items)
    {
        $ok = $items->getItemsWithoutProblems();
        if (count($ok) > 0) {
            $transfer = $this->transferSvc->create(
                $items->getSource(),
                $items->getDestination());
            foreach ($ok as $item) {
                $transfer->addBin($item->getBin());
            }
            $this->om->persist($transfer);
            $this->om->flush();

            $this->transferSvc->kit($transfer);
            $this->transferSvc->send($transfer);
            return $transfer;
        }
        return null;
    }

    /**
     * @param ReturnedItem[] $checkIn
     */
    public function resolveOutstanding(Facility $from, Facility $to)
    {
        $checkIn = $this->repo->findResolvedByLocations($from, $to);
        if (count($checkIn) == 0) {
            return null;
        }
        $transfer = $this->transferSvc->create($from, $to);
        foreach ($checkIn as $item) {
            $bin = $item->getBin();
            if ($bin->isAtLocation($from)) {
                $transfer->addBin($bin);
                $this->om->remove($item);
            } elseif ($bin->isAtLocation($to)) {
                // Nothing to transfer -- the bin is already in the right place.
                $this->om->remove($item);
            }
        }
        if ($transfer->isEmpty()) {
            $this->om->flush();
            return null;
        }

        $this->om->persist($transfer);
        $this->om->flush();
        $this->transferSvc->kit($transfer);
        $this->transferSvc->send($transfer);

        return $transfer;
    }
}
