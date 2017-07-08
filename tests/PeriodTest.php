<?php

namespace Applicazza\Appointed\Tests;

use Applicazza\Appointed\Period;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use function Applicazza\Appointed\today;

/**
 * Class PeriodTest
 * @package Applicazza\Appointed\Tests
 */
class PeriodTest extends TestCase
{
    public function testIsAfter()
    {
        $period1 = Period::make(today(10, 00), today(11, 00));
        $period2 = Period::make(today(8, 00), today(9, 00));

        $this->assertTrue($period1->isAfter($period2));
    }

    public function testIsBefore()
    {
        $period1 = Period::make(today(8, 00), today(9, 00));
        $period2 = Period::make(today(10, 00), today(11, 00));

        $this->assertTrue($period1->isBefore($period2));
    }

    public function testIsEnclosing()
    {
        $period1 = Period::make(today( 9, 00), today(12, 00));
        $period2 = Period::make(today(10, 00), today(11, 00));

        $this->assertTrue($period1->isEnclosing($period2));
    }

    public function testIsEndTouching()
    {
        $period1 = Period::make(today(9, 00), today(10, 00));
        $period2 = Period::make(today(10, 00), today(11, 00));

        $this->assertTrue($period1->isEndTouching($period2));
    }

    public function testIsIntersecting()
    {
        $period1 = Period::make(today(9, 00), today(10, 00));
        $period2 = Period::make(today(10, 00), today(11, 00));

        $this->assertTrue($period1->isIntersecting($period2));
    }

    public function testIsOverlapping()
    {
        $period1 = Period::make(today(9, 00), today(12, 00));
        $period2 = Period::make(today(10, 00), today(11, 00));

        $this->assertTrue($period1->isOverlapping($period2));
    }

    public function testIsStartTouching()
    {
        $period1 = Period::make(today(10, 00), today(11, 00));
        $period2 = Period::make(today(11, 00), today(12, 00));

        $this->assertTrue($period1->isEndTouching($period2));
    }

    public function testIsTheSameAs()
    {
        $period1 = Period::make(today(9, 00), today(10, 00));
        $period2 = Period::make(today(9, 00), today(10, 00));

        $this->assertTrue($period1->isTheSameAs($period2));
    }

    protected function setUp()
    {
        date_default_timezone_set('Asia/Jerusalem');
    }
}
