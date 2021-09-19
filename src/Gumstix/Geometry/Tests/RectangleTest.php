<?php


namespace Gumstix\Geometry\Tests;


use Gumstix\Geometry\Angle;
use Gumstix\Geometry\Rectangle;
use Gumstix\Geometry\Vector2D;
use PHPUnit\Framework\TestCase;

class RectangleTest extends TestCase
{
    /** @dataProvider oppositeProvider */
    public function testGetOpposite(Vector2D $origin,
                                    Vector2D $dimensions,
                                    Vector2D $expected)
    {
        $rect = Rectangle::fromDimensions($origin, $dimensions);
        $opposite = $rect->getOpposite();
        $this->assertTrue($expected->equals($opposite));
        $this->assertEquals($expected, $opposite);
    }

    public function oppositeProvider()
    {
        return [
            [
                new Vector2D(0, 0),
                new Vector2D(0, 0),
                new Vector2D(0, 0),
            ],
            [
                new Vector2D(2, 2),
                new Vector2D(4, 1),
                new Vector2D(6, 3),
            ],

            [
                new Vector2D(-2, -2),
                new Vector2D( 4,  1),
                new Vector2D( 2, -1),
            ],

            // rectangles can have negative dimensions
            [
                new Vector2D( 2,  2),
                new Vector2D(-1,  3),
                new Vector2D( 1,  5),
            ],
        ];
    }

    public function testContains()
    {
        $nully = Rectangle::fromCorners(new Vector2D(0, 0), new Vector2D(0, 0));
        $this->assertTrue($nully->contains($nully));

        $oneA = Rectangle::fromCorners(new Vector2D(0, 0), new Vector2D(1, 1));
        $oneB = Rectangle::fromCorners(new Vector2D(1, 1), new Vector2D(2, 2));
        $this->assertFalse($oneA->contains($oneB));
        $this->assertFalse($oneB->contains($oneA));

        $twoA = Rectangle::fromCorners(new Vector2D(0, 0), new Vector2D(2, 2));
        $twoB = Rectangle::fromCorners(new Vector2D(1, 1), new Vector2D(3, 3));
        $this->assertFalse($twoA->contains($twoB));
        $this->assertFalse($twoB->contains($twoA));

        $this->assertTrue($twoA->contains($oneA));
        $this->assertTrue($twoB->contains($oneB));

        $negOne = Rectangle::fromCorners(new Vector2D(-1, -1), new Vector2D(-2, -2));
        $negTwo = Rectangle::fromCorners(new Vector2D(-1, -1), new Vector2D(-3, -3));
        $this->assertTrue($negOne->contains($negOne));
        $this->assertTrue($negTwo->contains($negOne));
    }

    /**
     * @dataProvider overlapsProvider
     */
    public function testOverlaps(Rectangle $a, Rectangle $b, $expected)
    {
        $this->assertSame($expected, $a->overlaps($b));
        $this->assertSame($expected, $b->overlaps($a));
    }

    public function overlapsProvider()
    {
        // A zero-by-zero rectangle
        $nully = Rectangle::fromCorners(new Vector2D(0, 0), new Vector2D(0, 0));

        // Two one-by-one rectangles
        $oneA = Rectangle::fromCorners(new Vector2D(0, 0), new Vector2D(1, 1));
        $oneB = Rectangle::fromCorners(new Vector2D(1, 1), new Vector2D(2, 2));
        // oneC shares edges with oneA and oneB
        $oneC = Rectangle::fromCorners(new Vector2D(1, 0), new Vector2D(2, 1));

        // Two two-by-two rectangles
        $twoA = Rectangle::fromCorners(new Vector2D(0, 0), new Vector2D(2, 2));
        $twoB = Rectangle::fromCorners(new Vector2D(1, 1), new Vector2D(3, 3));

        // Two rectangles in the negative quandrant
        $negOne = Rectangle::fromCorners(new Vector2D(-1, -1), new Vector2D(-2, -2));
        $negTwo = Rectangle::fromCorners(new Vector2D(-1, -1), new Vector2D(-3, -3));

        return [
            [$nully, $nully, false], // a "null" rectangle does not overlap anything
            [$oneA, $oneA, true], // a rectangle always overlaps itself
            [$oneA, $oneB, false], // sharing a point does not constitute an overlap
            [$oneA, $oneC, false], // sharing edges not not constitute an overlap
            [$oneB, $oneC, false], // sharing edges not not constitute an overlap

            [$twoA, $twoB, true],
            [$oneA, $twoA, true],
            [$oneB, $twoB, true],
            [$negOne, $negTwo, true],
        ];
    }

    /** @dataProvider normalizeProvider */
    public function testNormalize($x1, $y1, $x2, $y2)
    {
        $orig = Rectangle::fromCorners(new Vector2D($x1, $y1), new Vector2D($x2, $y2));
        $norm = $orig->normalize();
        $this->assertTrue($orig->contains($norm));
        $this->assertTrue($norm->contains($orig));
    }

    public function normalizeProvider()
    {
        return [
            [ 0,  0, -1, -1],
            [ 0,  0,  1, -1],
            [-1, -1, -2, -2],
        ];
    }

    public function testEquals()
    {
        $a = Rectangle::fromCorners(new Vector2D(0, 0), new Vector2D(1, 1));
        $b = Rectangle::fromCorners(new Vector2D(1, 1), new Vector2D(0, 0));
        $c = Rectangle::fromCorners(new Vector2D(0, 0), new Vector2D(2, 1));

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
        $this->assertFalse($a->equals($c));
        $this->assertFalse($a->equals(null));
    }

    /** @dataProvider rotateProvider */
    public function testRotate(array $start, $degrees, array $end)
    {
        list($x1, $y1, $x2, $y2) = $start;
        $orig = Rectangle::fromCorners(
            new Vector2D($x1, $y1),
            new Vector2D($x2, $y2));

        $angle = Angle::degrees($degrees);

        list($x1, $y1, $x2, $y2) = $end;
        $expected = Rectangle::fromCorners(
            new Vector2D($x1, $y1),
            new Vector2D($x2, $y2));

        $result = $orig->rotate($angle);
        $this->assertTrue($expected->equals($result), "$expected vs $result");
    }

    public function rotateProvider()
    {
        return [
            [[0, 0, 1, 1],   0, [0, 0,  1,  1]],
            [[0, 0, 1, 1],  90, [0, 0, -1,  1]],
            [[0, 0, 1, 1], 180, [0, 0, -1, -1]],

            [[1, 1, 2, 3],   0, [1,  1,  2,  3]],
            [[1, 1, 2, 3], 270, [1, -1,  3, -2]],
        ];
    }
}
