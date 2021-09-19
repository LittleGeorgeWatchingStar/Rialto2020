<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class UserBoard
{
    /** @var int */
    private $time;

    /** @var string */
    private $userId;

    /** @var string|null */
    private $boardId;

    /** @var int */
    private $createdAt;

    /** @var int */
    private $updatedAt;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['user_id'],
            $body['board_id'],
            $body['created_at'],
            $body['updated_at'],
            $body['id']);
    }

    public function __construct(int $time,
                                string $userId,
                                ?string $boardId,
                                int $createdAt,
                                int $updatedAt,
                                string $id)
    {
        $this->time = $time;
        $this->userId = $userId;
        $this->boardId = $boardId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
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

    public function getBoardId(): ?string
    {
        return $this->boardId;
    }

    public function getCreatedAt(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->createdAt);
        return $dateTime;
    }

    public function getUpdatedAt(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->updatedAt);
        return $dateTime;
    }

    public function getId(): string
    {
        return $this->id;
    }
}