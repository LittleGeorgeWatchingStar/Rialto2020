<?php

namespace Rialto\Cms;

use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

/**
 * EngineInterface that renders CmsEntry objects directly.
 */
class CmsEngine implements EngineInterface
{
    /** @var CmsLoader */
    private $loader;

    /** @var Environment */
    private $environment;

    public function __construct(CmsLoader $loader, iterable $extensions)
    {
        $this->loader = $loader;
        $this->environment = new Environment($this->loader);
        foreach ($extensions as $extension) {
            $this->environment->addExtension($extension);
        }
    }

    public function exists($name)
    {
        return $this->loader->exists($name);
    }

    public function render($name, array $parameters = [])
    {
        return $this->environment->render($name, $parameters);
    }

    public function supports($name)
    {
        return $this->exists($name);
    }

}
