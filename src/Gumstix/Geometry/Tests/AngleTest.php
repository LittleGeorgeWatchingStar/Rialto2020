<?php


namespace Gumstix\Geometry\Tests;


use Gumstix\Geometry\Angle;
use Gumstix\Geometry\Vector2D;
use PHPUnit\Framework\TestCase;

class AngleTest extends TestCase
{
    /** @dataProvider rotateProvider */
    public function testRotate(Vector2D $vector, $degrees, Vector2D $expected)
    {
        $angle = Angle::degrees($degrees);
        $result = $angle->rotate($vector);
        $this->assertTrue($expected->equals($result));
    }

    public function rotateProvider()
    {
        return [
            [new Vector2D(1, 1),  90, new Vector2D(-1,  1)],

            [new Vector2D(1, 2),   0, new Vector2D( 1,  2)],
            [new Vector2D(1, 2),  90, new Vector2D(-2,  1)],
            [new Vector2D(1, 2), 180, new Vector2D(-1, -2)],
            [new Vector2D(1, 2), 270, new Vector2D( 2, -1)],
            [new Vector2D(1, 2), 360, new Vector2D( 1,  2)],
        ];
    }
}
