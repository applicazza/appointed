<?php

namespace Applicazza\Appointed\Tests;

use Applicazza\Appointed\Appointment;
use Applicazza\Appointed\BusinessDay;
use Applicazza\Appointed\Exceptions\OverlappingAppointmentException;
use Applicazza\Appointed\Exceptions\OverlappingBusinessDayHoursException;
use Applicazza\Appointed\Tests\Helpers\CarbonHelper as c;
use Applicazza\Appointed\Tests\Helpers\EchoHelper as e;
use PHPUnit\Framework\TestCase;

class BusinessDayTest extends TestCase
{
    public function testOverlappingAppointment()
    {
        $day = new BusinessDay(c::today(9), c::today(18));

        $day->addAppointments(
            new Appointment(c::today(10), c::today(11)),
            new Appointment(c::today(9), c::today(9, 30)),
            new Appointment(c::today(11, 45), c::today(12, 30))
        );

        $this->assertCount(3, $day->getAppointments());

        $this->expectException(OverlappingAppointmentException::class);

        $day->addAppointment(new Appointment(c::today(9, 30), c::today(10, 30)));
    }

    public function testOverlappingBusinessDayHoursException1()
    {
        $day = new BusinessDay(c::today(9), c::today(18));

        $day->addAppointments(
            new Appointment(c::today(10), c::today(11)),
            new Appointment(c::today(9), c::today(9, 30)),
            new Appointment(c::today(11, 45), c::today(12, 30))
        );

        $this->assertCount(3, $day->getAppointments());

        $this->expectException(OverlappingBusinessDayHoursException::class);

        $day->addAppointment(Appointment::make(c::today(8, 30), c::interval(0, 20)));
    }

    public function testOverlappingBusinessDayHoursException2()
    {
        $day = new BusinessDay(c::today(9), c::today(18));

        $day->addAppointments(
            new Appointment(c::today(10), c::today(11)),
            new Appointment(c::today(9), c::today(9, 30)),
            new Appointment(c::today(11, 45), c::today(12, 30))
        );

        $this->assertCount(3, $day->getAppointments());

        $this->expectException(OverlappingBusinessDayHoursException::class);

        $day->addAppointment(Appointment::make(c::today(9, 30), c::interval(10)));
    }

    public function testOverlappingAppointmentRecommendationAfter()
    {
        $day = new BusinessDay(c::today(9), c::today(18));

        $day->addAppointments(
            new Appointment(c::today(10), c::today(11)),
            new Appointment(c::today(9), c::today(9, 30)),
            new Appointment(c::today(11, 45), c::today(12, 30))
        );

        $this->assertCount(3, $day->getAppointments());

        try {
            $day->addAppointment(Appointment::make(c::today(11, 30), c::interval(2)));
        } catch (OverlappingAppointmentException $e) {
            $appointment = $day->offerAfter(Appointment::make(c::today(11, 30), c::interval(1)));
            e::info('Offered (after) between', $appointment->getStartsAt(), 'and', $appointment->getEndsAt());
        }
    }

    public function testOverlappingAppointmentRecommendationBefore()
    {
        $day = new BusinessDay(c::today(9), c::today(18));

        $day->addAppointments(
            new Appointment(c::today(9), c::today(9, 30)),
            new Appointment(c::today(9,45), c::today(10, 00)),
            new Appointment(c::today(10,45), c::today(12)),
            new Appointment(c::today(12, 30), c::today(12, 45))
        );

        $this->assertCount(4, $day->getAppointments());

        $desired_appointment = Appointment::make(c::today(11), c::interval(0, 15));

        try {
            $day->addAppointment($desired_appointment);
        } catch (OverlappingAppointmentException $e) {
            $appointment = $day->offerBefore($desired_appointment);
            if ($appointment)
                e::info('Offered (before) between', $appointment->getStartsAt(), 'and', $appointment->getEndsAt());
        }
    }

    protected function setUp()
    {
        date_default_timezone_set('Asia/Jerusalem');
    }
}
