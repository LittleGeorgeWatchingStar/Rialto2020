<?php

namespace Rialto\PcbNg\Email;


use Zend\Mail\Storage\Part\PartInterface;

final class OrderStatusEmail
{
    private $messageNo;

    private $messageId;

    /** @var string */
    private $rawPayload;

    public function __construct($messageNo, PartInterface $message)
    {
        $this->messageNo = $messageNo;
        $this->messageId = $message->getHeader('message-id', 'string');
        $this->rawPayload = (string)$message;
    }

    public function getMessageNo()
    {
        return $this->messageNo;
    }

    public function getMessageId()
    {
        return $this->messageId;
    }

    public function isOrderNotification(): bool
    {
        return (bool) json_decode($this->rawPayload, true);
    }

    public function getPayload(): ?array
    {
        return json_decode($this->rawPayload, true);
    }
}
