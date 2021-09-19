<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class NewPnpParsingResponse
{
    /** @var string */
    private $fileUrl;

    /** @var int */
    private $time;

    /** @var string */
    private $userId;

    /** @var string[][] */
    private $options;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['file_url'],
            $body['time'],
            $body['user_id'],
            $body['options'],
            $body['id']);
    }

    /**
     * @param string[][] $options
     */
    public function __construct(string $fileUrl,
                                int $time,
                                string $userId,
                                array $options,
                                string $id)
    {
        $this->fileUrl = $fileUrl;
        $this->time = $time;
        $this->userId = $userId;
        $this->options = $options;
        $this->id = $id;
    }

    public function getFileUrl(): string
    {
        return $this->fileUrl;
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
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getId(): string
    {
        return $this->id;
    }
}