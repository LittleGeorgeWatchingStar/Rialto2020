<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class NewRfqsResponse
{
    /** @var string */
    private $orderType;

    /** @var string */
    private $userId;

    /** @var string */
    private $boardId;

    /** @var string */
    private $serviceTier;

    /** @var string|null */
    private $communityCode;

    /** @var string */
    private $id;

    /** @var int */
    private $quantity;

    /** @var int */
    private $time;

    /** @var string */
    private $boardName;

    /** @var bool */
    private $econoEnable;

    /** @var array */
    private $shippingAddress;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['order_type'],
            $body['user_id'],
            $body['board_id'],
            $body['service_tier'],
            $body['community_code'],
            $body['id'],
            $body['quantity'],
            $body['time'],
            $body['board_name'],
            $body['econo_enable'],
            $body['shipping_address']);
    }

    public function __construct(string $orderType,
                                string $userId,
                                string $boardId,
                                string $serviceTier,
                                ?string $communityCode,
                                string $id,
                                int $quantity,
                                int $time,
                                string $boardName,
                                bool $econoEnable,
                                array $shippingAddress)
    {
        $this->orderType = $orderType;
        $this->userId = $userId;
        $this->boardId = $boardId;
        $this->serviceTier = $serviceTier;
        $this->communityCode = $communityCode;
        $this->id = $id;
        $this->quantity = $quantity;
        $this->time = $time;
        $this->boardName = $boardName;
        $this->econoEnable = $econoEnable;
        $this->shippingAddress = $shippingAddress;
    }

    public function getOrderType(): string
    {
        return $this->orderType;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getBoardId(): string
    {
        return $this->boardId;
    }

    public function getServiceTier(): string
    {
        return $this->serviceTier;
    }

    public function getCommunityCode(): ?string
    {
        return $this->communityCode;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTime(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->time);
        return $dateTime;
    }

    public function getBoardName(): string
    {
        return $this->boardName;
    }

    public function getEconoEnable(): bool
    {
        return $this->econoEnable;
    }

    public function getShippingAddress(): array
    {
        return $this->shippingAddress;
    }
}