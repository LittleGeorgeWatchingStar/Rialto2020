<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class OrderStatus
{
    /** @var int */
    private $time;

    /** @var string */
    private $userId;

    /** @var string */
    private $status;

    /** @var string */
    private $id;

    /** @var ?string */
    private $orderError;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['user_id'],
            $body['status'],
            $body['id'],
            $body['order_error']);
    }

    public function __construct(string $time,
                                string $userId,
                                string $status,
                                string $id,
                                ?string $orderError)
    {
        $this->time = $time;
        $this->userId = $userId;
        $this->status = $status;
        $this->id = $id;
        $this->orderError = $orderError;
    }

    public function getTime(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->time);
        return $dateTime;
    }


    public function getUserId(): string
    {
        return $this->userId;
    }


    public function getStatus(): string
    {
        return $this->status;
    }


    public function getId(): string
    {
        return $this->id;
    }

    public function getOrderError(): ?string
    {
        return $this->orderError;
    }
}