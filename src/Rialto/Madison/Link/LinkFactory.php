<?php

namespace Rialto\Madison\Link;

class LinkFactory
{
    public function createLink(string $title, string $iconUrl, string $url): array
    {
        return [
            'title' => $title,
            'icon' => $iconUrl,
            'url' => $url,
        ];
    }
}