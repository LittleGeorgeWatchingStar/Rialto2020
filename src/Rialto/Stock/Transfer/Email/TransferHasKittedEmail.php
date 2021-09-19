<?php

namespace Rialto\Stock\Transfer\Email;

use Rialto\Email\Mailable\Mailable;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Stock\Transfer\Transfer;

/**
 * Notifies the CM that a transfer has been kitted
 * and ready for pick up or to be send
 */
class TransferHasKittedEmail extends SubscriptionEmail
{
    const TOPIC = 'transferKittedResponse';

    /** @var Transfer*/
    private $transfer;

    public function __construct(Transfer $transfer, Mailable $sender)
    {
        $this->transfer = $transfer;
        $this->subject = "Gumstix Alert: {$this->transfer} is kitted";
        $this->template = 'stock/transfer/hasBeenKittedReadyForPickup.html.twig';
        $this->params = [
            'transfer' => $this->transfer,
            'transferItems' => $this->transfer->getLineItems(),
            'sender' => $sender,
        ];
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }
}