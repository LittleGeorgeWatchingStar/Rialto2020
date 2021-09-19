<?php

namespace Rialto\Stock\Transfer;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Shipping\Method\Orm\ShippingMethodRepository;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\StockEvents;
use Rialto\Stock\Transfer\Web\TransferReceipt;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manages Transfer lifecycle events.
 *
 * @see Transfer
 */
class TransferService
{
    /** @var ObjectManager */
    private $om;

    /** @var ShippingMethodRepository */
    private $shippingMethodRepo;

    /** @var TransferReceiver */
    private $receiver;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(ObjectManager $om,
                                TransferReceiver $receiver,
                                EventDispatcherInterface $dispatcher)
    {
        $this->om = $om;
        $this->shippingMethodRepo = $om->getRepository(ShippingMethod::class);
        $this->receiver = $receiver;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Creates a new empty transfer with "hand-carried" shipper.
     *
     * @return Transfer
     */
    public function create(Facility $from, Facility $to)
    {
        $transfer = Transfer::fromLocations($from, $to);
        $transfer->setShippingMethod($this->shippingMethodRepo->findHandCarried());
        return $transfer;
    }

    /**
     * Indicates that the items in $transfer have been put into a box
     * and that the transfer is ready for shipping.
     */
    public function kit(Transfer $transfer)
    {
        $transaction = Transaction::fromInitiator($transfer, $this->om);
        $transfer->kit($transaction);
        $this->om->persist($transaction);
        $this->om->flush();
        $this->notify($transfer, StockEvents::TRANSFER_KITTED);
    }

    public function inputTrackingNumber(Transfer $transfer, string $trackingNumber)
    {
        $transfer->setTrackingNumbers($trackingNumber);
        $this->notify($transfer, StockEvents::TRANSFER_ADD_A_TRACKING_NUM);
    }

    public function send(Transfer $transfer)
    {
        $transfer->send();
        if ($transfer->isAutoReceive()) {
            $this->autoReceive($transfer);
        }
        $this->notify($transfer, StockEvents::TRANSFER_SENT);
    }

    private function autoReceive(Transfer $transfer)
    {
        $receipt = new TransferReceipt($transfer);
        $this->receiver->receive($receipt);
    }

    private function notify(Transfer $transfer, $eventName)
    {
        $event = new TransferEvent($transfer);
        $this->dispatcher->dispatch($eventName, $event);
    }
}
