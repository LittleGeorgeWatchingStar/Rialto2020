<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class Quotes
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

    /** @var bool */
    private $includesSpecialHandlingPart;

    /** @var string */
    private $id;

    /** @var string */
    private $expiresAt;

    /** @var int */
    private $quantity;

    /** @var array */
    private $items;

    /** @var string */
    private $updateAt;

    /** @var int */
    private $time;

    /** @var string */
    private $boardName;

    /** @var string */
    private $totalPrice;

    /** @var bool */
    private $econoEnable;

    /** @var array */
    private $shippingAddress;

    /** @var bool */
    private $canBeOrdered;


    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['order_type'],
            $body['user_id'],
            $body['board_id'],
            $body['service_tier'],
            $body['community_code'],
            $body['includes_special_handling_part'] ?? false,
            $body['id'],
            $body['expires_at'],
            $body['quantity'],
            $body['items'],
            $body['updated_at'],
            $body['time'],
            $body['board_name'],
            $body['total_price'],
            $body['econo_enable'],
            $body['shipping_address'],
            $body['can_be_ordered']);
    }

    public function __construct(string $orderType,
                                string $userId,
                                string $boardId,
                                string $serviceTier,
                                ?string $communityCode,
                                bool $includesSpecialHandlingPart,
                                string $id,
                                string $expiresAt,
                                int $quantity,
                                array $items,
                                string $updateAt,
                                int $time,
                                string $boardName,
                                string $totalPrice,
                                bool $econoEnable,
                                array $shippingAddress,
                                bool $canBeOrdered)
    {
        $this->orderType = $orderType;
        $this->userId = $userId;
        $this->boardId = $boardId;
        $this->serviceTier = $serviceTier;
        $this->communityCode = $communityCode;
        $this->includesSpecialHandlingPart = $includesSpecialHandlingPart;
        $this->id = $id;
        $this->expiresAt = $expiresAt;
        $this->quantity = $quantity;
        $this->items = $items;
        $this->updateAt = $updateAt;
        $this->time = $time;
        $this->boardName = $boardName;
        $this->totalPrice = $totalPrice;
        $this->econoEnable = $econoEnable;
        $this->shippingAddress = $shippingAddress;
        $this->canBeOrdered = $canBeOrdered;
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

    public function getIncludesSpecialHandlingPart(): bool
    {
        return $this->includesSpecialHandlingPart;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getExpiresAt(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->expiresAt);
        return $dateTime;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getUpdateAt(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->updateAt);
        return $dateTime;
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

    public function getTotalPrice(): float
    {
        return (float)$this->totalPrice;
    }

    public function getEconoEnable(): bool
    {
        return $this->econoEnable;
    }

    public function getShippingAddress(): array
    {
        return $this->shippingAddress;
    }

    public function getCanBeOrdered(): bool
    {
        return $this->canBeOrdered;
    }
}