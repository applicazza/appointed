<?php

namespace Applicazza\Appointed\Tests;

use Applicazza\Appointed\Appointment;
use Applicazza\Appointed\BusinessDay;
use Applicazza\Appointed\Period;
use PHPUnit\Framework\TestCase;
use function Applicazza\Appointed\today;

class BusinessDayTest extends TestCase
{
    public function testInvalidOperatingPeriods()
    {
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today(9, 00), today(14, 00));
        $period_1300_1900 = Period::make(today(13, 00), today(19, 00));

        $this->assertFalse(
            $business_day->addOperatingPeriods(
                $period_1300_1900,
                $period_0900_1400
            ));
    }

    public function testValidOperatingPeriods()
    {
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today(9, 00), today(14, 00));
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

        $period_0900_1400 = Period::make(today(9, 00), today(14, 00));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $appointment_1100_1130 = Appointment::make(today(11, 00), today(11, 30));
        $appointment_1200_1330 = Appointment::make(today(12, 00), today(13, 30));

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

        $period_0900_1400 = Period::make(today(9, 00), today(14, 00));
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
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today(9, 00), today(14, 00));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $appointment_1100_1130 = Appointment::make(today(11, 00), today(11, 30));
        $appointment_1000_1330 = Appointment::make(today(10, 00), today(13, 30));

        $this->assertTrue(
            $business_day->addAppointments(
                $appointment_1100_1130
            ));

        $this->assertFalse(
            $business_day->addAppointments(
                $appointment_1000_1330
            ));
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

    public function testEditOperatingPeriod()
    {
        $business_day = new BusinessDay;

        $period_0800_1400 = Period::make(today( 8, 00), today(14, 00));
        $period_0900_1400 = Period::make(today( 9, 00), today(14, 00));
        $period_1330_1400 = Period::make(today(13, 30), today(14, 00));
        $period_1330_1430 = Period::make(today(13, 30), today(14, 30));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $appointment_0900_1130 = Appointment::make(today( 9, 00), today(11, 30));
        $appointment_1200_1330 = Appointment::make(today(12, 00), today(13, 30));

        $business_day->addAppointments(
            $appointment_0900_1130,
            $appointment_1200_1330
        );

        $this->assertTrue($business_day->editOperatingPeriod($period_1330_1400, $period_1330_1430));
        $this->assertFalse($business_day->editOperatingPeriod($period_0900_1400, $period_0800_1400));
    }

    public function testTrickyAppointments()
    {
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today(9, 00), today(14, 00));
        $period_1600_2000 = Period::make(today(16, 00), today(20, 00));

        $business_day->addOperatingPeriods(
            $period_0900_1400,
            $period_1600_2000
        );

        $appointment_0915_0930 = Appointment::make(today( 9, 15), today( 9, 30));
        $appointment_1115_1145 = Appointment::make(today(11, 15), today(11, 45));
        $appointment_1900_1930 = Appointment::make(today(19, 00), today(19, 30));

        $appointment_1130_1152 = Appointment::make(today(11, 30), today(11, 52));

        $business_day->addAppointments(
            $appointment_0915_0930,
            $appointment_1115_1145,
            $appointment_1900_1930
        );

        $this->assertFalse($business_day->addAppointments($appointment_1130_1152));

        $this->assertEquals(Appointment::make(today(10, 53), today(11, 15)), $business_day->fit($appointment_1130_1152, 'backward'));
        $this->assertEquals(Appointment::make(today(11, 45), today(12, 07)), $business_day->fit($appointment_1130_1152));
    }

    public function testDeleteAppointments()
    {
        $business_day = new BusinessDay;

        $period_0900_1400 = Period::make(today(9, 00), today(14, 00));
        $period_1600_1900 = Period::make(today(16, 00), today(19, 00));

        $business_day->addOperatingPeriods(
            $period_1600_1900,
            $period_0900_1400
        );

        $appointment_1100_1130 = Appointment::make(today(11, 00), today(11, 30));
        $appointment_1200_1330 = Appointment::make(today(12, 00), today(13, 30));

        $business_day->addAppointments(
            $appointment_1100_1130,
            $appointment_1200_1330
        );

        $this->assertTrue($business_day->deleteAppointments($appointment_1100_1130));
    }

    protected function setUp()
    {
        date_default_timezone_set('Asia/Jerusalem');
    }
}
