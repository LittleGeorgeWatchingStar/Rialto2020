<?php

namespace Gumstix\Geometry\Tests\Orm;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use Gumstix\Geometry\Orm\Vector2DType;
use Gumstix\Geometry\Vector2D;
use PHPUnit\Framework\TestCase;

class Vector2DTypeTest extends TestCase
{
    public function testGetName()
    {
        $type = $this->makeType();
        $this->assertSame('vector2d', $type->getName());
    }

    /**
     * @return Type|Vector2DType
     */
    private function makeType(): Vector2DType
    {
        if (!Type::hasType(Vector2DType::NAME)) {
            Type::addType(Vector2DType::NAME, Vector2DType::class);
        }
        return Type::getType(Vector2DType::NAME);
    }

    public function testGetSqlDeclaration()
    {
        $type = $this->makeType();
        $fieldDeclaration = [];
        $result = $type->getSQLDeclaration($fieldDeclaration, $this->defaultPlatform());
        $this->assertSame('VARCHAR(255)', $result);
    }

    private function defaultPlatform(): AbstractPlatform
    {
        return new MySqlPlatform();
    }

    public function testConvertToDatabaseValue_null_isNull()
    {
        $type = $this->makeType();
        $result = $type->convertToDatabaseValue(null, $this->defaultPlatform());
        $this->assertNull($result);
    }

    public function testConvertToDatabaseValue_vector_isJsonString()
    {
        $type = $this->makeType();
        $vector = new Vector2D(1.3, -2.6);
        $result = $type->convertToDatabaseValue($vector, $this->defaultPlatform());
        $this->assertSame('{"x":1.3,"y":-2.6}', $result);
    }

    public function testConvertToPhpValue_null_isNull()
    {
        $type = $this->makeType();
        $result = $type->convertToPHPValue(null, $this->defaultPlatform());
        $this->assertNull($result);
    }

    public function testConvertToPhpValue_valid_isVector()
    {
        $type = $this->makeType();
        $valid = '{"y":-2.6,"x":0}';
        $result = $type->convertToPHPValue($valid, $this->defaultPlatform());
        $this->assertInstanceOf(Vector2D::class, $result);
        $this->assertEquals(0, $result->getX());
        $this->assertEquals(-2.6, $result->getY());
    }
}
