<?php

namespace Rialto\Web;


final class DomainName
{
    /**
     * Returns the domain name without the subdomain.
     *
     * @param string $url Eg, "http://www.digikey.com"
     * @return string Eg "digikey.com"
     */
    public static function parse($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            throw new \InvalidArgumentException("'$url' is a malformed URL");
        }
        $parts = explode('.', $host);
        $parts = array_slice($parts, -2, 2);
        return join('.', $parts);
    }
}
