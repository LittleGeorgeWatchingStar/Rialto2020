<?php

namespace Rialto\Manufacturing\Kit\Reminder;


use Doctrine\Common\Persistence\ObjectManager;
use Gumstix\Time\Time;
use JMS\JobQueueBundle\Entity\Job;
use Rialto\Stock\StockEvents;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Schedules an email to be sent to the CM if a transfer is not checked in
 * within a reasonable timeframe.
 */
class EmailScheduler implements EventSubscriberInterface
{
    const DELAY = '+1 weekday';

    /**
     * @var ObjectManager
     */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public static function getSubscribedEvents()
    {
        return [
            StockEvents::TRANSFER_SENT => 'schedulePesterEmailIfNeeded',
        ];
    }

    public function schedulePesterEmailIfNeeded(TransferEvent $event)
    {
        $transfer = $event->getTransfer();
        if ($this->shouldScheduleEmail($transfer)) {
            $this->scheduleEmail($transfer);
        }
    }

    private function shouldScheduleEmail(Transfer $transfer)
    {
        return (!$transfer->isReceived())
            && ($transfer->isForSupplier());
    }

    private function scheduleEmail(Transfer $transfer)
    {
        $job = new Job(SendEmailCommand::NAME, [$transfer->getId()]);
        $time = Time::now()->format('H:i:s');
        $job->setExecuteAfter(Time::getTime(self::DELAY . $time));
        $this->om->persist($job);
    }
}
