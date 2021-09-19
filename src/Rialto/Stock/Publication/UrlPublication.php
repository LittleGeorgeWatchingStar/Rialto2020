<?php

namespace Rialto\Stock\Publication;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A publication that is available via URL.
 */
class UrlPublication extends Publication
{
    /**
     * @return string
     *
     * @Assert\NotBlank
     * @Assert\Url
     */
    public function getUrl()
    {
        return $this->content;
    }

    public function setUrl($url)
    {
        $this->content = trim($url);
    }
}
