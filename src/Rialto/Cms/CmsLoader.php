<?php

namespace Rialto\Cms;

use Doctrine\Common\Persistence\ObjectManager;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Loader\SourceContextLoaderInterface;
use Twig\Source;

/**
 * A Twig loader that loads templates from the CMS table.
 */
class CmsLoader implements
    LoaderInterface,
    SourceContextLoaderInterface
{
    /** @var ObjectManager */
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function getCacheKey($name)
    {
        return $name;
    }

    public function exists($name)
    {
        return (bool) $this->getEntry($name);
    }

    private function getEntry(string $name): ?CmsEntry
    {
        return $this->em->find(CmsEntry::class, $name);
    }

    /**
     * @throws LoaderError
     */
    public function getSource(string $name): string
    {
        $entry = $this->getEntry($name);
        if (! $entry ) {
            throw new LoaderError("No such CMS entry '$name'");
        }
        return $entry->getFormattedContent();
    }

    /**
     * Returns the source context for a given template logical name.
     *
     * @param string $name The template logical name
     *
     * @return Source
     *
     * @throws LoaderError When $name is not found
     */
    public function getSourceContext($name)
    {
        return new Source($this->getSource($name), $name);
    }

    public function isFresh($name, $time)
    {
        return false;
    }
}
