<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class UnzippedBundle
{
    /** @var int */
    private $time;

    /** @var string */
    private $bundleId;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['bundle_id'],
            $body['id']);
    }

    public function __construct(int $time,
                                string $bundleId,
                                string $id)
    {
        $this->time = $time;
        $this->bundleId = $bundleId;
        $this->id = $id;
    }

    public function getTime(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->time);
        return $dateTime;
    }

    public function getBundleId(): string
    {
        return $this->bundleId;
    }

    public function getId(): string
    {
        return $this->id;
    }
}