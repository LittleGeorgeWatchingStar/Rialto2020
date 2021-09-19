<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class PostBundleForUnzippingResponse
{
    /** @var int */
    private $time;

    /** @var string */
    private $userId;

    /** @var string */
    private $filename;

    /** @var string */
    private $zipFileUrl;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['user_id'],
            $body['filename'],
            $body['zip_file_url'],
            $body['id']);
    }

    public function __construct(int $time,
                                string $userId,
                                string $filename,
                                string $zipFileUrl,
                                string $id)
    {
        $this->time = $time;
        $this->userId = $userId;
        $this->filename = $filename;
        $this->zipFileUrl = $zipFileUrl;
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

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getZipFileUrl(): string
    {
        return $this->zipFileUrl;
    }

    public function getId(): string
    {
        return $this->id;
    }
}