<?php

namespace Rialto\PcbNg\Command;


use Rialto\PcbNg\Email\OrderStatusEmail;
use Rialto\PcbNg\PcbNgOrderNotificationAdapter;
use Rialto\PcbNg\Service\PcbNgMailbox;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ProcessPcbNgEmailsHandler
{
    /** @var PcbNgMailbox */
    private $mailbox;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(PcbNgMailbox $mailbox,
                                EventDispatcherInterface $eventDispatcher)
    {
        $this->mailbox = $mailbox;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(ProcessPcbNgEmailsCommand $command): void
    {
        $emails = $this->mailbox->getOrderNotifications();
        foreach ($emails as $email) {
            $this->processEmail($email);
        }
    }

    private function processEmail(OrderStatusEmail $email): void
    {
        $event = PcbNgOrderNotificationAdapter::toEvent($email->getPayload());
        $this->eventDispatcher->dispatch(get_class($event), $event);
        $this->mailbox->markMessageHandled($email->getMessageId());
    }
}
