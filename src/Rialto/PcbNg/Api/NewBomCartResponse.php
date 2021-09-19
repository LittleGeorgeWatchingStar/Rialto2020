<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class NewBomCartResponse
{
    /** @var int */
    private $time;

    /** @var string */
    private $userId;

    /** @var array[][] */
    private $items;

    /** @var string|null */
    private $error;

    /** @var int */
    private $kitQty;

    /** @var bool */
    private $econoEnable;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['user_id'],
            $body['items'],
            $body['error'] ?? null,
            $body['kit-qty'],
            $body['econo_enable'],
            $body['id']);
    }

    /**
     * @param string[][] $items
     */
    public function __construct(int $time,
                                string $userId,
                                array $items,
                                ?string $error,
                                int $kitQty,
                                bool $econoEnable,
                                string $id)
    {
        $this->time = $time;
        $this->userId = $userId;
        $this->items = $items;
        $this->error = $error;
        $this->kitQty = $kitQty;
        $this->econoEnable = $econoEnable;
        $this->id = $id;
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

    /**
     * @return string[][]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getKitQty(): int
    {
        return $this->kitQty;
    }

    public function getEconoEnable(): bool
    {
        return $this->econoEnable;
    }

    public function getId(): string
    {
        return $this->id;
    }
}