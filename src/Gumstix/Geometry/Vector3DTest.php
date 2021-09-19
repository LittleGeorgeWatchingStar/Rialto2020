<?php

namespace Gumstix\Geometry;


use PHPUnit\Framework\TestCase;

class Vector3DTest extends TestCase
{
    public function testToArray()
    {
        $v = new Vector3D(3, 1.1, 0);
        $result = $v->toArray();
        $this->assertEquals(['x' => 3, 'y' => 1.1, 'z' => 0], $result);
    }

    public function testAdd()
    {
        $v1 = new Vector3D(1, 2, 3);
        $v2 = new Vector3D(4, 4, 4);

        $result = $v1->add($v2);

        $this->assertEquals(5, $result->getX());
        $this->assertEquals(6, $result->getY());
        $this->assertEquals(7, $result->getZ());
    }
}
