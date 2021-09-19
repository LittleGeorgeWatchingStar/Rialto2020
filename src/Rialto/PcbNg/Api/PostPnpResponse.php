<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class PostPnpResponse
{
    /** @var int */
    private $time;

    /** @var string */
    private $userId;

    /** @var string */
    private $fileUrl;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['user_id'],
            $body['file_url'],
            $body['id']);
    }

    public function __construct(int $time,
                                string $userId,
                                string $fileUrl,
                                string $id)
    {
        $this->time = $time;
        $this->userId = $userId;
        $this->fileUrl = $fileUrl;
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

    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }

    public function getId(): string
    {
        return $this->id;
    }
}