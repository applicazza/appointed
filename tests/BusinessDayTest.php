<?php

namespace Applicazza\Appointed\Tests;

use Applicazza\Appointed\Appointment;
use Applicazza\Appointed\BusinessDay;
use Applicazza\Appointed\Period;
use function Applicazza\Appointed\today;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BusinessDayTest extends TestCase
{
    public function testInvalidOperatingPeriods()
    {
        $this->expectException(InvalidArgumentException::class);

        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today( 9, 00), today(14, 00));
        $period_1300_1900 = Period::make(today(13, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1300_1900,
            $period_0900_1400
        );
    }

    public function testValidOperatingPeriods()
    {
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today( 9, 00), today(14, 00));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $this->assertEquals([$period_0900_1400, $period_1600_1900], $business_day->getAgenda());
    }

    public function testValidAppointments()
    {
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today( 9, 00), today(14, 00));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $appointment_1100_1130 = Appointment::make(today( 11, 00), today(11, 30));
        $appointment_1200_1330 = Appointment::make(today( 12, 00), today(13, 30));

        $business_day->addAppointments(
            $appointment_1100_1130,
            $appointment_1200_1330
        );

        $this->assertEquals([
            Period::make(today(9, 00), today(11, 00)),
            Appointment::make(today(11, 00), today(11, 30)),
            Period::make(today(11, 30), today(12, 00)),
            Appointment::make(today(12, 00), today(13, 30)),
            Period::make(today(13, 30), today(14, 00)),
            Period::make(today(16, 00), today(19, 00)),
        ], $business_day->getAgenda());
    }

    public function testAnotherValidAppointments()
    {
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today( 9, 00), today(14, 00));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $appointment_0900_1130 = Appointment::make(today(  9, 00), today(11, 30));
        $appointment_1200_1330 = Appointment::make(today( 12, 00), today(13, 30));

        $business_day->addAppointments(
            $appointment_0900_1130,
            $appointment_1200_1330
        );

        $this->assertEquals([
            Appointment::make(today(9, 00), today(11, 30)),
            Period::make(today(11, 30), today(12, 00)),
            Appointment::make(today(12, 00), today(13, 30)),
            Period::make(today(13, 30), today(14, 00)),
            Period::make(today(16, 00), today(19, 00)),
        ], $business_day->getAgenda());
    }

    public function testInvalidAppointments()
    {
        $this->expectException(InvalidArgumentException::class);

        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today( 9, 00), today(14, 00));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $appointment_1100_1130 = Appointment::make(today( 11, 00), today(11, 30));
        $appointment_1000_1330 = Appointment::make(today( 10, 00), today(13, 30));

        $business_day->addAppointments(
            $appointment_1100_1130,
            $appointment_1000_1330
        );
    }

    public function testFit()
    {
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today(9, 00), today(14, 00));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $appointment_0900_1130 = Appointment::make(today(9, 00), today(11, 30));
        $appointment_1200_1330 = Appointment::make(today(12, 00), today(13, 30));
        $appointment_1300_1330 = Appointment::make(today(13, 00), today(13, 30));

        $business_day->addAppointments(
            $appointment_0900_1130,
            $appointment_1200_1330
        );

        $this->assertEquals(Appointment::make(today(13, 30), today(14, 00)), $business_day->fit($appointment_1300_1330, 'forward'));
        $this->assertEquals(Appointment::make(today(11, 30), today(12, 00)), $business_day->fit($appointment_1300_1330, 'backward'));
    }

    public function testDeleteOperatingPeriod()
    {
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today(9, 00), today(14, 00));
        $period_1330_1400 = Period::make(today(13, 30), today(14, 00));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $appointment_0900_1130 = Appointment::make(today(9, 00), today(11, 30));
        $appointment_1200_1330 = Appointment::make(today(12, 00), today(13, 30));

        $business_day->addAppointments(
            $appointment_0900_1130,
            $appointment_1200_1330
        );

        $this->assertTrue($business_day->deleteOperatingPeriod($period_1600_1900));
        $this->assertFalse($business_day->deleteOperatingPeriod($period_0900_1400));
        $this->assertTrue($business_day->deleteOperatingPeriod($period_1330_1400));
    }

    protected function setUp()
    {
        date_default_timezone_set('Asia/Jerusalem');
    }
}
