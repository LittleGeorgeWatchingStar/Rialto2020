<?php

namespace Rialto\Stock\Transfer\Email;

use Rialto\Email\Mailable\Mailable;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Stock\Transfer\Transfer;

/**
 * Notifies the CM that a transfer has been kitted
 * and ready for pick up or to be send
 */
class TransferHasTrackingNumberEmail extends SubscriptionEmail
{
    const TOPIC = 'transferGotTrackingNumber';

    /** @var Transfer*/
    private $transfer;

    public function __construct(Transfer $transfer, Mailable $sender)
    {
        $this->transfer = $transfer;
        $trackingNumber = $this->transfer->getTrackingNumber();
        $this->subject = "Gumstix Alert: {$this->transfer} is kitted";
        $this->template = 'stock/transfer/hasBeenKittedAndGotTrackingNumber.html.twig';
        $this->params = [
            'transfer' => $this->transfer,
            'transferItems' => $this->transfer->getLineItems(),
            'sender' => $sender,
            'trackingNum' => $trackingNumber,
        ];
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }
}