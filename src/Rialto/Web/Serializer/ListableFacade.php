<?php

namespace Rialto\Web\Serializer;

use Traversable;

/**
 * Facades can use this trait to easily instatiates lists of themselves.
 */
trait ListableFacade
{
    /**
     * @param array|Traversable $list
     * @return static[]
     */
    public static function fromList($list)
    {
        $result = [];
        foreach ($list as $object) {
            $result[] = new static($object);
        }
        return $result;
    }
}
