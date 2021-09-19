<?php

namespace Gumstix\Time;

use DateTimeInterface as Date;
use DateTime;
use PHPUnit\Framework\TestCase;


class DateRangeTest extends TestCase
{
    public function testCreate_withNoDates_hasNoStartDate()
    {
        $range = DateRange::create();
        $this->assertFalse($range->hasStart());
    }

    public function testCreate_withNoDates_hasNoEndDate()
    {
        $range = DateRange::create();
        $this->assertFalse($range->hasEnd());
    }

    public function testCreate_withStart_hasStartDate()
    {
        $range = DateRange::create()
            ->withStart('today');
        $this->assertTrue($range->hasStart());
        $this->assertInstanceOf(Date::class, $range->getStart());
    }

    public function testCreate_withStart_hasNoEndDate()
    {
        $range = DateRange::create()
            ->withStart('today');
        $this->assertFalse($range->hasEnd());
        $this->assertNull($range->getEnd());
    }

    public function test_withRelativeDates_hasCorrectDates()
    {
        $range = DateRange::create()
            ->withStart('-1 year')
            ->withEnd('-1 day');

        $this->assertDatesAreCloseEnough('-1 year', $range->getStart());
        $this->assertDatesAreCloseEnough('-1 day', $range->getEnd());
    }

    private function assertDatesAreCloseEnough($expected, Date $actual)
    {
        $expected = new \DateTimeImmutable($expected);
        static $fmt = 'Y-m-d H:i:s';
        $this->assertSame($expected->format($fmt), $actual->format($fmt));
    }

    public function test_with_createsNewInstance()
    {
        $range0 = DateRange::create();
        $range1 = $range0->withStart('-1 year');
        $range2 = $range1->withEnd('-1 day');

        $this->assertNotSame($range0, $range1);
        $this->assertNotSame($range1, $range2);
    }

    /**
     * To avoid mutable object errors.
     */
    public function test_with_mutableDatesAreCloned()
    {
        $range1 = DateRange::create()->withStart(new \DateTime('-1 day'));
        $range2 = $range1->withEnd(new \DateTime('now'));

        $this->assertNotSame($range1->getStart(), $range2->getStart());
    }

    public function test_with_unmodifiedDatesArePreserved()
    {
        $range1 = DateRange::create()->withStart(new \DateTime('-1 day'));
        $range2 = $range1->withEnd(new \DateTime('now'));

        $this->assertEquals($range1->getStart(), $range2->getStart());
    }

    /**
     * @dataProvider containsProvider
     */
    public function testContains_works($start, $end, $test, $expected)
    {
        $range = DateRange::create()
            ->withStart($start)
            ->withEnd($end);

        $result = $range->contains(new \DateTime($test));
        $this->assertSame($expected, $result, "$range vs $test");
    }

    public function containsProvider()
    {
        return [
            // open-ended date range
            [null, null, 'now', true],
            [null, null, '-100 years', true],
            [null, null, '+100 years', true],

            // from 6 months ago until the end of time
            ['-6 months', null, 'now', true],
            ['-6 months', null, '+100 years', true],
            ['-6 months, 00:00:00.0', null, '-6 months, 00:00:00.0', true],
            ['-6 months', null, '-7 months', false],
            ['-6 months', null, '-100 years', false],

            // from the beginning of time until 6 months ago
            [null, '-6 months', 'now', false],
            [null, '-6 months', '-5 months', false],
            [null, '-6 months', '-6 months, 00:00:00.0', true],
            [null, '-6 months', '-7 months', true],
            [null, '-6 months', '-100 years', true],

            // between 12 and 6 months ago
            ['-12 months', '-6 months', 'now', false],
            ['-12 months', '-6 months', '-5 months', false],
            ['-12 months', '-6 months', '-6 months, 00:00:00.0', true],
            ['-12 months', '-6 months', '-7 months', true],
            ['-12 months', '-6 months', '-12 months', true],
            ['-12 months', '-6 months', '-13 months', false],
            ['-12 months', '-6 months', '-100 years', false],
        ];
    }

    public function testFormatStart_isNull_returnsBlank()
    {
        $range = DateRange::create();
        $result = $range->formatStart('Y-m-d');
        $this->assertSame('', $result);
    }

    public function testFormatStart_notNull_returnsString()
    {
        $range = DateRange::create()
            ->withStart('2015-04-12');
        $result = $range->formatStart('Y-m-d');
        $this->assertSame('2015-04-12', $result);
    }

    public function testFormatEnd_isNull_returnsBlank()
    {
        $range = DateRange::create();
        $result = $range->formatEnd('j');
        $this->assertSame('', $result);
    }

    public function testFormatEnd_notNull_returnsString()
    {
        $range = DateRange::create()
            ->withStart('2015-04-12');
        $result = $range->formatStart('M');
        $this->assertSame('Apr', $result);
    }

    /**
     * Make sure we're creating defensive copies of mutable date objects.
     */
    public function testGetStart_returnsCopy()
    {
        $original = '2015-04-12';
        $range = DateRange::create()
            ->withStart(new DateTime($original));
        /** @var DateTime $start */
        $start = $range->getStart();
        $start->modify('+1 day');
        $this->assertSame($original, $range->getStart()->format('Y-m-d'));
    }

    /**
     * Make sure we're creating defensive copies of mutable date objects.
     */
    public function testGetEnd_returnsCopy()
    {
        $original = '2015-04-12';
        $range = DateRange::create()
            ->withEnd(new DateTime($original));
        /** @var DateTime $end */
        $end = $range->getEnd();
        $end->modify('+1 day');
        $this->assertSame($original, $range->getEnd()->format('Y-m-d'));
    }
}
