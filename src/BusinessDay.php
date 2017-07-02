<?php

namespace Applicazza\Appointed;

use Applicazza\Appointed\Exceptions\InvalidAppointmentException;
use Applicazza\Appointed\Exceptions\OverlappingAppointmentException;
use Applicazza\Appointed\Exceptions\OverlappingBusinessDayHoursException;
use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * Class BusinessDay
 * @package Applicazza\Appointed
 */
class BusinessDay
{
    /**
     * @var \Carbon\Carbon
     */
    protected $starts_at;

    /**
     * @var \Carbon\Carbon
     */
    protected $ends_at;

    /**
     * @var \Applicazza\Appointed\Appointment[]
     */
    protected $appointments = [];

    /**
     * @var \Applicazza\Appointed\Slot[]
     */
    protected $available_slots = [];

    /**
     * BusinessDay constructor.
     * @param \Carbon\Carbon $starts_at
     * @param \Carbon\Carbon $ends_at
     */
    public function __construct(Carbon $starts_at, Carbon $ends_at)
    {
        $this->starts_at = $starts_at;
        $this->ends_at = $ends_at;
    }

    /**
     * @param \Carbon\Carbon $starts_at
     * @param \Carbon\CarbonInterval $duration
     * @return static
     */
    public static function make(Carbon $starts_at, CarbonInterval $duration)
    {
        return new static($starts_at, (new Carbon($starts_at))->add($duration));
    }

    /**
     * @param \Applicazza\Appointed\Appointment $new_appointment
     * @return $this
     * @throws \Applicazza\Appointed\Exceptions\OverlappingAppointmentException
     * @throws \Applicazza\Appointed\Exceptions\OverlappingBusinessDayHoursException
     */
    public function addAppointment(Appointment $new_appointment)
    {
        if ($new_appointment->isOverlappingWithBusinnessHours($this)) {
            throw new OverlappingBusinessDayHoursException;
        }

        foreach ($this->appointments as &$appointment) {

            if ($appointment->isOverlappingWith($new_appointment)) {
                throw new OverlappingAppointmentException;
            }
        }

        $this->appointments[] = $new_appointment;
        $this->sortAppointments();
        $this->calculateEmptySlots();
        return $this;
    }

    /**
     * Sorts appointments
     */
    protected function sortAppointments()
    {
        usort($this->appointments, function ($a, $b) {
            if ($a == $b) return 0;
            return $a < $b ? -1 : 1;
        });
    }

    /**
     * Calculate empty slots
     */
    protected function calculateEmptySlots()
    {
        $available_slots = [];

        $previous_appointment = null;

        foreach ($this->appointments as &$appointment) {

            // First element
            if ($appointment === reset($this->appointments)) {

                if ($appointment->getStartsAt()->notEqualTo($this->getStartsAt())) {
                    $available_slots[] = new Slot($this->getStartsAt(), $appointment->getStartsAt());
                }

                $previous_appointment = $appointment;
                continue;
            }

            // Last element
            if ($appointment === end($this->appointments)) {

                if ($previous_appointment->getEndsAt()->notEqualTo($appointment->getStartsAt())) {
                    $available_slots[] = new Slot($previous_appointment->getEndsAt(), $appointment->getStartsAt());
                }

                if ($appointment->getEndsAt()->notEqualTo($this->getEndsAt())) {
                    $available_slots[] = new Slot($appointment->getEndsAt(), $this->getEndsAt());
                }

                continue;
            }

            if ($previous_appointment->getEndsAt()->notEqualTo($appointment->getStartsAt())) {
                $available_slots[] = new Slot($previous_appointment->getEndsAt(), $appointment->getStartsAt());
            }

            $previous_appointment = &$appointment;
        }

        $this->available_slots = $available_slots;
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getStartsAt()
    {
        return $this->starts_at;
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getEndsAt()
    {
        return $this->ends_at;
    }

    /**
     * @param array ...$new_appointments
     * @return $this
     * @throws \Applicazza\Appointed\Exceptions\InvalidAppointmentException
     * @throws \Applicazza\Appointed\Exceptions\OverlappingAppointmentException
     * @throws \Applicazza\Appointed\Exceptions\OverlappingBusinessDayHoursException
     */
    public function addAppointments(... $new_appointments)
    {
        $pending_appointments = [];

        if (is_array($new_appointments) && is_array($new_appointments[0]))
            $new_appointments = $new_appointments[0];

        foreach ($new_appointments as $new_appointment) {

            if (!is_object($new_appointment) || get_class($new_appointment) !== Appointment::class)
                throw new InvalidAppointmentException;

            if ($new_appointment->isOverlappingWithBusinnessHours($this)) {
                throw new OverlappingBusinessDayHoursException;
            }

            foreach ($this->appointments as &$appointment) {
                if ($appointment->isOverlappingWith($new_appointment)) {
                    throw new OverlappingAppointmentException;
                }
            }

            $pending_appointments[] = $new_appointment;
        }

        if (count($pending_appointments))
            $this->appointments = array_merge($this->appointments, $pending_appointments);

        $this->sortAppointments();
        $this->calculateEmptySlots();
        return $this;
    }

    /**
     * @return \Applicazza\Appointed\Appointment[]
     */
    public function getAppointments()
    {
        return $this->appointments;
    }

    /**
     * @param \Applicazza\Appointed\Appointment $appointment
     * @return \Applicazza\Appointed\Appointment|null
     */
    public function offerAfter(Appointment $appointment)
    {
        $offered_appointment = null;

        foreach ($this->getAvailableSlots() as $available_slot) {
            if ($available_slot->getStartsAt()->lt($appointment->getStartsAt()) || $available_slot->getDuration()->seconds < $appointment->getDuration()->seconds)
                continue;
            else {
                $offered_appointment = Appointment::make($available_slot->getStartsAt(), $appointment->getDuration());
                break;
            }
        }

        return $offered_appointment;
    }

    /**
     * @return \Applicazza\Appointed\Slot[]
     */
    public function getAvailableSlots()
    {
        return $this->available_slots;
    }

    /**
     * @param \Applicazza\Appointed\Appointment $appointment
     * @return \Applicazza\Appointed\Appointment|null
     */
    public function offerBefore(Appointment $appointment)
    {
        $available_slots = $this->getAvailableSlots();
        rsort($available_slots);

        $offered_appointment = null;

        foreach ($available_slots as $available_slot) {
            if ($available_slot->getStartsAt()->gt($appointment->getStartsAt()) || $available_slot->getDuration()->seconds < $appointment->getDuration()->seconds) {
                continue;
            } else {
                $offered_appointment = Appointment::make($available_slot->getStartsAt(), $appointment->getDuration());
                break;
            }
        }

        return $offered_appointment;
    }
}