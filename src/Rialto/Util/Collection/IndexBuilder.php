<?php

namespace Rialto\Util\Collection;

use DateTime;
use SplObjectStorage as Map;

class IndexBuilder
{
    private static $dateIndex = [];

    /**
     * Builds an index of objects from $list keyed by the value
     * returned by $method.
     *
     * @param Iterable<object> $list
     * @param string $method
     * @return Map<object, array>
     */
    public static function fromObjects($list, $method)
    {
        $index = new Map();
        foreach ( $list as $object ) {
            $key = $object->$method();
            $key = self::normalize($key);
            $list = isset($index[$key]) ? $index[$key] : [];
            $list[] = $object;
            $index[$key] = $list;
        }
        return $index;
    }

    private static function normalize($key)
    {
        if ( $key instanceof DateTime ) {
            return self::getNormalizedDate($key);
        }
        return $key;
    }

    private static function getNormalizedDate(DateTime $date)
    {
        $ts = $date->getTimestamp();
        if (! isset(self::$dateIndex[$ts]) ) {
            self::$dateIndex[$ts] = $date;
        }
        return self::$dateIndex[$ts];
    }
}
