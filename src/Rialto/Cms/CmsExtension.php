<?php

namespace Rialto\Cms;

use Rialto\Web\TwigExtensionTrait;
use Twig\Extension\AbstractExtension;

/**
 * Twig extension for the internal content management system (CMS).
 *
 * Templates can use this extension to inject snippets of dynamic content.
 */
class CmsExtension extends AbstractExtension
{
    use TwigExtensionTrait;

    /** @var CmsEngine */
    private $cmsEngine;

    public function __construct(CmsEngine $engine)
    {
        $this->cmsEngine = $engine;
    }

    public function getFunctions()
    {
        return [
            $this->simpleFunction('rialto_cms_entry', 'getContent', ['html']),
        ];
    }

    public function getContent($entryId, array $params = [])
    {
        if (! $this->cmsEngine->exists($entryId) ) {
            throw new CmsException("Missing required CMS entry \"$entryId\"");
        }
        return $this->cmsEngine->render($entryId, $params);
    }
}
