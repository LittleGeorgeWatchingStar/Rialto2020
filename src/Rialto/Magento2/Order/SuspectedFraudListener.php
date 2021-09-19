<?php

namespace Rialto\Magento2\Order;

use Rialto\Email\Email;
use Rialto\Email\Mailable\GenericMailable;
use Rialto\Email\MailerInterface;
use Rialto\Magento2\Magento2Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Emails us when Magento detects an order that is suspected fraud.
 */
class SuspectedFraudListener implements EventSubscriberInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            Magento2Events::SUSPECTED_FRAUD => 'notifyOfSuspectedFraud',
        ];
    }

    public function notifyOfSuspectedFraud(SuspectedFraudEvent $event)
    {
        $mail = new Email();
        $mail->setFrom(new GenericMailable('security@gumstix.com', 'Gumstix security'));
        $mail->addTo(new GenericMailable('admin@gumstix.com', 'Gumstix admin'));
        $orderNo = sprintf('Magento order %s', $event->getCustomerReference());
        $mail->setSubject("Suspected fraud: $orderNo");
        $mail->setBody("I just received $orderNo, which Magento has flagged as suspected fraud.");
        $this->mailer->send($mail);
    }

}
