<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Exception\PcbNgClientException;

class Bundle
{
    /** @var int */
    private $time;

    /** @var string */
    private $filename;

    /** @var array */
    private $namesUrls;

    /** @var string */
    private $id;

    /**
     * $names_url[filename] => url
     * @var array
     */
    private $namesUrlsMap;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self(
            $body['time'],
            $body['filename'],
            $body['names_urls'],
            $body['id']);
    }

    public function __construct(int $time,
                                string $filename,
                                array $namesUrls,
                                string $id)
    {
        $this->time = $time;
        $this->filename = $filename;
        $this->namesUrls = $namesUrls;
        $this->id = $id;

        $this->namesUrlsMap = [];
        foreach ($namesUrls as $nameUrl) {
            $name = $nameUrl[0];
            $url = $nameUrl[1];
            $this->namesUrlsMap[$name] = $url;
        }
    }

    public function getTime(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->time);
        return $dateTime;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getNamesUrls(): array
    {
        return $this->namesUrls;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function hasFilename(string $name): bool
    {
        return isset($this->namesUrlsMap[$name]);
    }

    public function getUrl(string $name): string
    {
        assert(isset($this->namesUrlsMap[$name]));
        return $this->namesUrlsMap[$name];
    }
}