<?php

namespace Rialto\Security\Web;


use Rialto\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;

class SecurityExtension extends AbstractExtension
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return [
            new \Twig_Filter('user_date', [$this, 'userDate'], [
                'is_safe' => ['html']
            ]),
            new \Twig_Filter('user_datetime', [$this, 'userDatetime'], [
                'is_safe' => ['html']
            ]),
        ];
    }

    public function userDatetime(\DateTime $date = null)
    {
        return $this->userDate($date, ' H:i:s');
    }

    public function userDate(\DateTime $date = null, $timeFormat = '')
    {
        if (null === $date) {
            return '<span class="null">none</span>';
        }
        $user = $this->getUserOrNull();
        $format = $user ? $user->getDateFormat() : 'Y-m-d';
        $format .= $timeFormat;
        return $date->format($format);
    }

    /** @return User|null */
    private function getUserOrNull()
    {
        $token = $this->tokenStorage->getToken();
        return $token ? $token->getUser() : null;
    }
}
