<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;

class Bom
{
    /** @var int */
    private $time;

    /** @var string */
    private $userId;

    /** @var string */
    private $bomQuoteId;

    /** @var string */
    private $bundleId;

    /** @var string */
    private $pnpDataId;

    /** @var array */
    private $placement;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['user_id'],
            $body['bom_quote_id'],
            $body['bundle_id'],
            $body['pnp-data-id'],
            $body['placement'],
            $body['id']);
    }

    /**
     * @param PcbNgPart[] $parts
     */
    public function __construct(string $time,
                                string $userId,
                                string $bomQuoteId,
                                string $bundleId,
                                string $pnpDataId,
                                array $placements,
                                string $id)
    {
        $this->time = $time;
        $this->userId = $userId;
        $this->bomQuoteId = $bomQuoteId;
        $this->bundleId = $bundleId;
        $this->pnpDataId = $pnpDataId;
        $this->placement = $placements;
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

    public function getBomQuoteId(): string
    {
        return $this->bomQuoteId;
    }

    public function getBundleId(): string
    {
        return $this->bundleId;
    }

    public function getPnpDataId(): string
    {
        return $this->pnpDataId;
    }

    public function getPlacement(): array
    {
        return $this->placement;
    }

    public function getId(): string
    {
        return $this->id;
    }
}