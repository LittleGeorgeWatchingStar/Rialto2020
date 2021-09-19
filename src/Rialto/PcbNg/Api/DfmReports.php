<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class DfmReports
{
    /** @var int */
    private $time;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['id']);
    }

    /**
     * @param PcbNgPart[] $parts
     */
    public function __construct(string $time,
                                string $id)
    {
        $this->time = $time;
        $this->id = $id;
    }

    public function getTime(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->time);
        return $dateTime;
    }

    public function getId(): string
    {
        return $this->id;
    }
}