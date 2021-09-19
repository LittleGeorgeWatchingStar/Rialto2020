<?php

namespace Rialto\Manufacturing\WorkOrder;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\MailerInterface;
use Rialto\Manufacturing\WorkOrder\Orm\WorkOrderRepository;
use Rialto\Stock\StockEvents;
use Rialto\Stock\Transfer\Email\TransferHasKittedEmail;
use Rialto\Stock\Transfer\Email\TransferHasTrackingNumberEmail;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\TransferEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransferEventListener implements EventSubscriberInterface
{
    /** @var WorkOrderRepository */
    private $repo;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var ObjectManager
     */
    private $om;

    public function __construct(ObjectManager $om, MailerInterface $mailer)
    {
        $this->repo = $om->getRepository(WorkOrder::class);
        $this->mailer = $mailer;
        $this->om = $om;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            StockEvents::TRANSFER_SENT => 'updateOrders',
            StockEvents::TRANSFER_RECEIPT => 'updateOrders',
            StockEvents::MISSING_ITEM_RESOLVED => 'updateOrders',
        ];
    }

    public function transferKittedResponse(TransferEvent $event)
    {
        $this->updateOrders($event);
        $transfer = $event->getTransfer();
        $shippingMethod = $transfer->getShippingMethod();
        $manualShipping = [TransferRepository::HAND_CARRIED, TransferRepository::TRUCK];

        if ($shippingMethod && in_array($shippingMethod->getCode(), $manualShipping)) {
            $this->transferKittedEmail($event);
        }
    }

    public function transferGotTrackingNumber(TransferEvent $event)
    {
        $this->updateOrders($event);
        $this->transferHasTrackingNumberEmail($event);
    }

    /**
     * Mark any related work orders as updated.
     */
    public function updateOrders(TransferEvent $event)
    {
        $affectedOrders = $this->repo->findByTransfer($event->getTransfer());
        foreach ($affectedOrders as $wo) {
            $wo->setUpdated();
        }
    }

    /**
     * send email to CM as soon as a Transfer is kitted.
     */
    public function transferKittedEmail(TransferEvent $event)
    {
        $transfer = $event->getTransfer();
        $email = new TransferHasKittedEmail($transfer, EmailPersonality::BobErbauer());
        $email->addTo($transfer->getDestination()->getContact());
        $email->setFrom(EmailPersonality::BobErbauer());
        $email->loadSubscribers($this->om);
        if ($email->hasRecipients()) {
            $this->mailer->send($email);
        }
    }

    /**
     * send email to CM as soon as a Transfer has got a tracking number.
     */
    public function transferHasTrackingNumberEmail(TransferEvent $event)
    {
        $transfer = $event->getTransfer();
        $email = new TransferHasTrackingNumberEmail($transfer, EmailPersonality::BobErbauer());
        $email->addTo($transfer->getDestination()->getContact());
        $email->setFrom(EmailPersonality::BobErbauer());
        $email->loadSubscribers($this->om);
        if ($email->hasRecipients()) {
            $this->mailer->send($email);
        }
    }
}
