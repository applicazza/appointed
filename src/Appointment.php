<?php

namespace Applicazza\Appointed;

use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * Class Appointment
 * @package Applicazza\Appointed
 */
class Appointment extends Slot
{
    /**
     * @param \Applicazza\Appointed\Appointment $appointment
     * @return bool
     */
    public function isOverlappingWith(Appointment $appointment)
    {
        return $appointment->getEndsAt()->gt($this->getStartsAt()) && $this->getEndsAt()->gt($appointment->getStartsAt());
    }

    public function isOverlappingWithBusinnessHours(BusinessDay $business_day)
    {
        return $this->getStartsAt()->lt($business_day->getStartsAt()) || $this->getEndsAt()->gt($business_day->getEndsAt());
    }
}