<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class PnpData
{
    /** @var PcbNgPart[] */
    private $parts;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            array_map(function (array $part) {
                return new PcbNgPart(
                    $part['Identifier'],
                    $part['X'],
                    $part['Y'],
                    $part['Side'],
                    $part['Rotation'],
                    $part['Digi-Key SKU'] ?? null);
            }, $body['parts']),
            $body['id']);
    }

    /**
     * @param PcbNgPart[] $parts
     */
    public function __construct(array $parts,
                                string $id)
    {
        $this->parts = $parts;
        $this->id = $id;
    }

    /**
     * @return PcbNgPart[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    public function getId(): string
    {
        return $this->id;
    }
}