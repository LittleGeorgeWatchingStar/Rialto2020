<?php


namespace Gumstix\Geometry;


use PHPUnit\Framework\TestCase;

class Vector2DTest extends TestCase
{
    /** @dataProvider addProvider */
    public function testAdd($x1, $y1, $dx, $dy, $ex, $ey)
    {
        $v = new Vector2D($x1, $y1);
        $d = new Vector2D($dx, $dy);
        $r = $v->add($d);

        $this->assertNotSame($v, $r);
        $this->assertNotSame($d, $r);

        $this->assertSame($ex, $r->getX());
        $this->assertSame($ey, $r->getY());
    }

    public function addProvider()
    {
        return [
            [0,   0,     0,   0,   0.0, 0.0],
            [0,   0,     2,   1,   2.0, 1.0],
            [0.1, 0.5,   2,   1,   2.1, 1.5],
            [3.0, 3.0,  -1.5, 0.5, 1.5, 3.5],
        ];
    }

    /** @dataProvider equalsProvider */
    public function testEquals(Vector2D $v1, $v2, $expected)
    {
        $this->assertSame($expected, $v1->equals($v2));
    }

    public function equalsProvider()
    {
        return [
            [new Vector2D(0, 0), new Vector2D(0, 0),     true],
            [new Vector2D(0, 0), null,                   false],
            [new Vector2D(0, 0), new Vector2D(1, 0),     false],
            [new Vector2D(0, 0), new Vector2D(0.0, 0.0), true],
            [new Vector2D("1.0", "1.00"), new Vector2D(1.0, 1.0), true],
        ];
    }

    public function testToArray()
    {
        $v = new Vector2D(3, 1.1);
        $result = $v->toArray();
        $this->assertEquals(['x' => 3, 'y' => 1.1], $result);
    }
}
