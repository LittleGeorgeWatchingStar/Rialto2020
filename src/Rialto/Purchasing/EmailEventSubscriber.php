<?php

namespace Rialto\Purchasing;

use Rialto\Email\MailerInterface;
use Rialto\Purchasing\Order\Email\OrderRejectedEmail;
use Rialto\Purchasing\Order\Event\PurchaseOrderRejected;
use Rialto\Purchasing\Receiving\GoodsReceivedEvent;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Rialto\Purchasing\Receiving\Notify\OrderReceivedEmail;
use Rialto\Security\User\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Subscribes to events which might require that emails or
 * other communications be sent.
 */
class EmailEventSubscriber implements EventSubscriberInterface
{
    /** @var MailerInterface */
    private $mailer;

    /** @var TokenStorageInterface */
    private $tokens;

    function __construct(
        MailerInterface $mailer,
        TokenStorageInterface $tokens)
    {
        $this->mailer = $mailer;
        $this->tokens = $tokens;
    }

    public static function getSubscribedEvents()
    {
        return [
            PurchaseOrderRejected::class => 'onOrderRejected',
            PurchasingEvents::GOODS_RECEIVED => 'onGoodsReceived',
        ];
    }

    public function onOrderRejected(PurchaseOrderRejected $event)
    {
        $order = $event->getPurchaseOrder();
        assertion(null !== $order->getOwner());
        $email = new OrderRejectedEmail($order, $this->getUser());
        $this->mailer->send($email);
    }

    private function getUser()
    {
        $token = $this->tokens->getToken();
        $user = $token ? $token->getUser() : null;
        assertion($user instanceof User);
        return $user;
    }

    public function onGoodsReceived(GoodsReceivedEvent $event)
    {
        $owner = $event->getOrderOwner();
        if ($event->shouldNotifyOwner() && $owner->getEmail()) {
            $this->sendEmail($owner, $event->getGrn());
        }
    }

    private function sendEmail(User $owner, GoodsReceivedNotice $grn)
    {
        $email = new OrderReceivedEmail($owner, $grn);
        $this->mailer->send($email);
    }
}
