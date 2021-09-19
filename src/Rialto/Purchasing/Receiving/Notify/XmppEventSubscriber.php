<?php

namespace Rialto\Purchasing\Receiving\Notify;


use Fabiang\Xmpp\Client as XmppClient;
use Fabiang\Xmpp\Exception\ExceptionInterface as XmppException;
use Fabiang\Xmpp\Protocol\Message;
use Fabiang\Xmpp\Protocol\Presence;
use Psr\Log\LoggerInterface;
use Rialto\Purchasing\PurchasingEvents;
use Rialto\Purchasing\Receiving\GoodsReceivedEvent;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Rialto\Security\User\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sends XMPP (Google chat) messages in response to PO receipts.
 */
class XmppEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var XmppClient
     */
    private $xmppClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(XmppClient $xmppClient, LoggerInterface $logger)
    {
        $this->xmppClient = $xmppClient;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            PurchasingEvents::GOODS_RECEIVED => 'sendXmppMessageIfNeeded',
        ];
    }

    public function sendXmppMessageIfNeeded(GoodsReceivedEvent $event)
    {
        $owner = $event->getOrderOwner();
        if ($event->shouldNotifyOwner() && $owner->getXmpp()) {
            $this->tryToSendXmppMessage($owner, $event->getGrn());
        }
    }

    private function tryToSendXmppMessage(User $owner, GoodsReceivedNotice $grn)
    {
        try {
            $this->sendXmppMessage($owner, $grn);
        } catch (XmppException $ex) {
            $this->handleError($ex, $owner);
        } catch (\ErrorException $ex) {
            $this->handleError($ex, $owner);
        }
    }

    private function sendXmppMessage(User $owner, GoodsReceivedNotice $grn)
    {
        $messageText = $this->buildMessage($grn);
        $this->xmppClient->connect();
        $this->xmppClient->send(new Presence());
        $message = new Message($messageText, $owner->getXmpp());
        $this->xmppClient->send($message);
        $this->xmppClient->disconnect();
    }

    /**
     * @return string The XMPP message body
     */
    private function buildMessage(GoodsReceivedNotice $grn)
    {
        $lines = [];
        $lines[] = sprintf("%s just received %s:",
            $grn->getReceiver(),
            $grn->getDescription());

        foreach ($grn->getItems() as $grnItem) {
            $lines[] = sprintf("%s of %s",
                number_format($grnItem->getQtyReceived(), 0),
                $grnItem->getDescription());
        }
        return join("\r", $lines);
    }

    private function handleError(\Exception $ex, User $owner)
    {
        $msg = sprintf("Unable to send XMPP message to %s: %s",
            $owner->getXmpp(),
            $ex->getMessage());
        $this->logger->error($msg);
    }
}
