<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class PnpReview
{
    /** @var string[] */
    private $headers;

    /** @var string[][] */
    private $rows;

    /** @var string[][] */
    private $options;

    /** @var string */
    private $id;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['headers'],
            $body['rows'],
            $body['options'],
            $body['id']);
    }

    /**
     * @param string[] $headers
     * @param string[][] $rows
     * @param string[][] $options
     */
    public function __construct(array $headers,
                                array $rows,
                                array $options,
                                string $id)
    {
        $this->headers = $headers;
        $this->rows = $rows;
        $this->options = $options;
        $this->id = $id;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string[][]
     */
    public function getRows(): array
    {
        return $this->rows;
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