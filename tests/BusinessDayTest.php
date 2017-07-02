<?php

namespace Applicazza\Appointed\Tests;

use Applicazza\Appointed\BusinessDay;
use Applicazza\Appointed\Period;
use PHPUnit\Framework\TestCase;
use function \today;

class BusinessDayTest extends TestCase
{
    public function testBusinessDayCanAcceptDesiredPeriod()
    {
        $business_day = $this->createBusinessDay();

        $period = new Period(today(9, 30), today(10, 30));

        $this->assertTrue($business_day->isAvailableAt($period));
    }

    public function testBusinessDayCannotAcceptDesiredPeriod()
    {
        $business_day = $this->createBusinessDay();

        $period = new Period(today(10, 30), today(11, 30));

        $this->assertTrue($business_day->isNotAvailableAt($period));

        $this->assertEquals(Period::make(today(11, 00), interval(1)), $business_day->closestFor($period));

        $this->assertEquals(Period::make(today( 9, 30), interval(1)), $business_day->closestFor($period, true));
    }

    protected function setUp()
    {
        date_default_timezone_set('Asia/Jerusalem');
    }

    protected function createBusinessDay()
    {
        return (new BusinessDay)
            ->addBusinessHours(
                new Period(today( 8, 00), today(14, 00)),
                new Period(today(16, 00), today(19, 00))
            )->addOccupiedSlots(
                new Period(today( 9, 15), today( 9, 30)),
                new Period(today(10, 30), today(11, 00)),
                new Period(today(16, 00), today(17, 00))
            );
    }
}
