<?php

namespace Rialto\PcbNg\Service;


use Rialto\Email\MailerInterface;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\PcbNg\Event\Cancelled;
use Rialto\PcbNg\Event\Error;
use Rialto\PcbNg\Event\InFabrication;
use Rialto\PcbNg\Event\InManufacturing;
use Rialto\PcbNg\Event\OnHold;
use Rialto\PcbNg\Event\PcbNgEventEmail;
use Rialto\PcbNg\Event\PendingReview;
use Rialto\PcbNg\Event\QueuedForFabrication;
use Rialto\PcbNg\Event\QueuedForManufacturing;
use Rialto\PcbNg\Event\Refunded;
use Rialto\PcbNg\Event\Shipped;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Forward notifications from PCB:NG to a generic subscription email for initial
 * testing purposes.
 */
final class PcbNgNotificationEmailer implements EventSubscriberInterface
{
    /** @var MailerInterface */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            PendingReview::class => 'onPendingReview',
            OnHold::class => 'onHold',
            Cancelled::class => 'onCancelled',
            QueuedForFabrication::class => 'onQueuedForFabrication',
            QueuedForManufacturing::class => 'onQueuedForManufacturing',
            InFabrication::class => 'onInFabrication',
            InManufacturing::class => 'onInManufacturing',
            Shipped::class => 'onShipped',
            Refunded::class => 'onRefunded',
            Error::class => 'onError',
        ];
    }

    private function sendEmail(SubscriptionEmail $email): void
    {
        $this->mailer->loadSubscribers($email);
        $this->mailer->send($email);
    }

    public function onPendingReview(PendingReview $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }

    public function onHold(OnHold $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }

    public function onCancelled(Cancelled $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }

    public function onQueuedForFabrication(QueuedForFabrication $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }

    public function onQueuedForManufacturing(QueuedForManufacturing $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }

    public function onInFabrication(InFabrication $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }

    public function onInManufacturing(InManufacturing $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }

    public function onShipped(Shipped $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }

    public function onRefunded(Refunded $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }

    public function onError(Error $event): void
    {
        $email = new PcbNgEventEmail($event->getPayload());
        $this->sendEmail($email);
    }
}
