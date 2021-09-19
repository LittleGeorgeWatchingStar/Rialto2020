<?php

namespace Rialto\Madison;


use Psr\Http\Message\ResponseInterface;

class MadisonException extends \RuntimeException
{
    public static function fromResponse(ResponseInterface $response)
    {
        return new static(static::createMessage($response), $response->getStatusCode());
    }

    private static function createMessage(ResponseInterface $response)
    {
        return sprintf('Madison returned status %d (%s): %s',
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $response->getBody());
    }

    public function isStatus($statusCode)
    {
        return $this->getCode() == $statusCode;
    }
}
