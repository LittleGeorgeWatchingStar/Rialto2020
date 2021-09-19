<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class Board
{
    /** @var int */
    private $time;

    /** @var string */
    private $userId;

    /** @var string */
    private $bundleId;

    /** @var string */
    private $bomId;

    /** @var bool */
    private $pcbDfmReportsRequested;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['user_id'],
            $body['bundle_id'],
            $body['bom_id'],
            $body['pcb_dfm_reports_requested'],
            $body['id']);
    }

    public function __construct(int $time,
                                string $userId,
                                string $bundleId,
                                string $bomId,
                                string $pcbDfmReportsRequested,
                                string $id)
    {
        $this->time = $time;
        $this->userId = $userId;
        $this->bundleId = $bundleId;
        $this->bomId = $bomId;
        $this->pcbDfmReportsRequested = $pcbDfmReportsRequested;
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

    public function getBundleId(): string
    {
        return $this->bundleId;
    }

    public function getBomId(): string
    {
        return $this->bomId;
    }

    public function isPcbDfmReportsRequested(): bool
    {
        return $this->pcbDfmReportsRequested;
    }

    public function getId(): string
    {
        return $this->id;
    }
}