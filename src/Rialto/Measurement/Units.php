<?php

namespace Rialto\Measurement;

/**
 * Represents a unit of measure.
 */
class Units
{
    const EACH = 'each';
    const PACK = 'pack';
    const METRES = 'metres';
    const LITRES = 'litres';
    const LENGTH = 'length';
    const KG = 'kg';

    private $name;

    private static $precision = [
        self::EACH => 0,
        self::PACK => 0,
        self::METRES => 3,
        self::LITRES => 3,
        self::LENGTH => 3,
        self::KG => 4,
    ];

    public static function getChoices()
    {
        $keys = array_keys(self::$precision);
        return array_combine($keys, $keys);
    }

    /** @return Units */
    public static function each()
    {
        return new self(self::EACH);
    }

    public function __construct($name)
    {
        $name = strtolower($name);
        if (! isset(self::$precision[$name]) ) {
            throw new \InvalidArgumentException("No such unit of measure $name");
        }
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getPrecision()
    {
        return self::$precision[$this->name];
    }
}
