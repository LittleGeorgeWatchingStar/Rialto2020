<?php

namespace Rialto\Sales\Order\Email;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\MailerInterface;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\SalesEvents;
use Rialto\Security\User\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sends emails based on sales order lifecycle events.
 */
class OrderEmailListener implements EventSubscriberInterface
{
    /** @var OrderToEmailFilter */
    private $orderToEmailFilter;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $managerUsername;

    public function __construct(MailerInterface $mailer, ObjectManager $om, string $manager, OrderToEmailFilter $filter)
    {
        $this->mailer = $mailer;
        $this->om = $om;
        $this->managerUsername = $manager;
        $this->orderToEmailFilter = $filter;
    }

    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::ORDER_ALLOCATED => 'notifyIfUnableToAllocate',
            SalesEvents::ORDER_AUTHORIZED => 'notifyWhenCustomerHasPaid'
        ];
    }

    /**
     * Notify the purchasing manager if a sales order cannot be allocated
     * from anything (stock or work orders).
     */
    public function notifyIfUnableToAllocate(SalesOrderEvent $event)
    {
        $order = $event->getOrder();
        $status = $order->getAllocationStatus();
        if (!$status->isFullyAllocated()) {
            $this->notifyPurchasingManager($order);
        }
    }

    private function notifyPurchasingManager(SalesOrder $order)
    {
        $manager = $this->getPurchasingManager();
        $email = new UnallocatedOrderEmail($order, $manager);
        $this->mailer->send($email);
    }

    /**
     * @return User|object
     */
    private function getPurchasingManager()
    {
        return $this->om->find(User::class, $this->managerUsername);
    }

    /**
     * Notify the engineers and managers if a sales order is charged, AKA
     * the customer has paid
     */
    public function notifyWhenCustomerHasPaid(SalesOrderEvent $event){
        $order = $event->getOrder();
        $result = $this->orderToEmailFilter->IsEmailNecessary($order);

        if ($result){
            $so = $event->getOrder();
            $email = new HasPaidEmail($so, EmailPersonality::BobErbauer());
            $email->setFrom(EmailPersonality::BobErbauer());
            $email->loadSubscribers($this->om);

            if ($email->hasRecipients()) {
                $this->send($email);
            }
        }
    }

    protected function send(Email $email)
    {
        $this->mailer->send($email);
    }
}
