<?php

namespace Rialto;

use Exception;

/**
 * Thrown when an error occurs accessing a network resource.
 */
class NetworkException
extends ResourceException
{
    private $uri;

    /**
     * @param string $uri
     *  The URI path that generated the exception.
     * @param string $message (optional)
     *  A custom error message.
     * @param Exception $previous (optional)
     *  The previous exception that triggered this one.
     */
    public function __construct($uri, $message = null, Exception $previous = null)
    {
        $this->uri = $uri;

        if (! $message ) $message = "Error accessing $uri";
        $code = $previous ? $previous->getCode() : 0;
        parent::__construct($message, $code);
    }

    public function getUri()
    {
        return $this->uri;
    }
}
