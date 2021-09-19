<?php

namespace Rialto\Alert;

/**
 * An alert resolution that simply shows a link.
 */
class LinkResolution implements AlertResolution
{
    private $uri;
    private $text;

    public function __construct($uri, $text = 'Click here to resolve the problem.')
    {
        $this->uri = $uri;
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
