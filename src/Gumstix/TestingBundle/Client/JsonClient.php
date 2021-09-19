<?php

namespace Gumstix\TestingBundle\Client;

use Symfony\Component\HttpFoundation\Response;

/**
 * A subclass of TestClient for testing JSON API endpoints.
 */
class JsonClient extends TestClient
{
    /** @return array */
    public function decodeBody(Response $response)
    {
        return json_decode($response->getContent(), true);
    }

    public function formatBody(Response $response)
    {
        return json_encode($this->decodeBody($response), JSON_PRETTY_PRINT);
    }
}
