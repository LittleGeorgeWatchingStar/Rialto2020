<?php

namespace Rialto\Web;


use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;

abstract class EntityLinkExtension extends AbstractExtension
{
    use TwigExtensionTrait;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    protected function linkIfGranted($privilege, $url, $label, $target = null)
    {
        if ($this->auth->isGranted($privilege)) {
            return $this->link($url, $label, $target);
        }
        return $label;
    }

}
