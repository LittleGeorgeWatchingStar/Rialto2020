<?php

namespace Gumstix\Time;


use PHPUnit\Framework\TestCase;

class TimeTest extends TestCase
{
    public function testTime_parameterIsString()
    {
        $date = '2017-01-01';
        $dateTime = new \DateTime($date);
        Time::setTime($date);
        $result = Time::now();

        $this->assertEquals($dateTime, $result);
    }

    public function testTime_parameterIsDateTimeObject()
    {
        $date = '2017-01-01';
        $dateTime = new \DateTime($date);
        Time::setTime($dateTime);
        $result = Time::now();

        $this->assertEquals($dateTime, $result);
    }

    public function test_getTime()
    {
        $date = '1997-05-13';
        Time::setTime($date);
        $dateTime = new \DateTime('1997-05-13');
        $result = Time::getTime($date);

        $this->assertEquals($dateTime, $result);
    }

    public function testNow_returnsDateTime()
    {
        $result = Time::now();
        $this->assertInstanceOf(\DateTime::class, $result);
    }

    public function test_getTimeWithOffset_returnsOffsetOfCustomTime()
    {
        $offset = '+24 hours';
        $date = '2017-01-01';
        Time::setTime($date);
        $result = Time::getTime($offset);
        $expectedTime = new \DateTime('2017-01-02');

        $this->assertEquals($expectedTime, $result);
    }
}
