<?php

namespace Rialto\Stock\Item\Version\Web;

use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Orm\ItemVersionRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ItemVersionToSkuTransformer implements DataTransformerInterface
{
    /** @var ItemVersionRepository */
    private $repo;

    public function __construct(ItemVersionRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param ItemVersion|null $itemVersion
     * @return string|null
     */
    public function transform($itemVersion)
    {
        return $itemVersion ? $itemVersion->getFullSku() : null;
    }

    /**
     * @param string $string
     * @return
     */
    public function reverseTransform($string)
    {
        $string = strtoupper(trim($string));
        if (! $string) {
            return null;
        }

        $itemVersion = $this->repo->findByFullSku($string);
        if (! $itemVersion) {
            $msg = "No such item-version $string";
            throw new TransformationFailedException($msg);
        }
        return $itemVersion;
    }

}
