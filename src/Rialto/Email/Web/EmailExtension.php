<?php

namespace Rialto\Email\Web;

use Rialto\Web\TwigExtensionTrait;
use Twig\Extension\AbstractExtension;

/**
 * Twig extension for email-related things.
 */
class EmailExtension extends AbstractExtension
{
    use TwigExtensionTrait;

    public function getFilters()
    {
        return [
            $this->simpleFilter('email', 'mailtoLink', ['html']),
            $this->simpleFilter('telephone', 'telephoneLink', ['html']),
        ];
    }

    public function mailtoLink($email)
    {
        if (!$email) {
            return $this->none();
        }
        $enc = htmlentities($email);
        return sprintf('<a href="mailto:%s">%s</a>', $enc, $enc);
    }

    public function telephoneLink($phoneNumber)
    {
        if (!$phoneNumber) {
            return $this->none();
        }
        $enc = htmlentities($phoneNumber);
        return sprintf('<a href="tel:%s">%s</a>', $enc, $enc);
    }
}
