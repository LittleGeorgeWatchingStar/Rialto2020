<?php

namespace Gumstix\Geometry\Orm;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Gumstix\Geometry\Vector2D;

/**
 * For persisting Vector2D objects to a database column.
 */
class Vector2DType extends Type
{
    const NAME = 'vector2d';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param Vector2D|null $value
     * @param AbstractPlatform $platform
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value ? json_encode($value->toArray()) : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }
        $array = json_decode($value, true);
        return new Vector2D($array['x'], $array['y']);
    }

    public function getName()
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
