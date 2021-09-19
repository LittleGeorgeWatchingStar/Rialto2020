<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class PostOrderResponse
{
    /** @var string */
    private $orderRfqId;

    /** @var string */
    private $userBoardId;

    /** @var array */
    private $shippingAddress;

    /** @var bool */
    private $requireOrderInvoice;

    /** @var string */
    private $userId;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $id;


    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['order_rfq_id'],
            $body['userboard_id'],
            $body['shipping_address'],
            $body['require_order_invoice'],
            $body['user_id'],
            $body['created_at'],
            $body['id']);
    }

    public function __construct(string $orderRfqId,
                                string $userBoardId,
                                array $shippingAddress,
                                bool $requireOrderInvoice,
                                string $userId,
                                string $createdAt,
                                string $id)
    {
        $this->orderRfqId = $orderRfqId;
        $this->userBoardId = $userBoardId;
        $this->shippingAddress = $shippingAddress;
        $this->requireOrderInvoice = $requireOrderInvoice;
        $this->userId = $userId;
        $this->createdAt = $createdAt;
        $this->id = $id;
    }

    public function getOrderRfqId(): string
    {
        return $this->orderRfqId;
    }

    public function getUserBoardId(): string
    {
        return $this->userBoardId;
    }


    public function getShippingAddress(): array
    {
        return $this->shippingAddress;
    }

    public function isRequireOrderInvoice(): bool
    {
        return $this->requireOrderInvoice;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getCreatedAt(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->createdAt);
        return $dateTime;
    }

    public function getId(): string
    {
        return $this->id;
    }
}