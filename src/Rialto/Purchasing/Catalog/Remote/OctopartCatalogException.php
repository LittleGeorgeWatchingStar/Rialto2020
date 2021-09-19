<?php

namespace Rialto\Purchasing\Catalog\Remote;

use Psr\Http\Message\ResponseInterface;

class OctopartCatalogException extends \RuntimeException implements SupplierCatalogException
{
    public static function fromHttpResponse(ResponseInterface $response)
    {
        $msg = sprintf("Octopart catalog returned status %s: %s",
            $response->getStatusCode(),
            $response->getBody()
        );
        return new self($msg);
    }
}
