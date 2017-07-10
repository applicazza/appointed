<?php

namespace Applicazza\Appointed;

/**
 * Class BusinessDay
 * @package Applicazza\Appointed
 */
class BusinessDay
{
    /**
     * @var \Applicazza\Appointed\Agenda
     */
    protected $agenda;

    /**
     * BusinessDay constructor.
     */
    function __construct()
    {
        $this->agenda = new Agenda;
    }

    /**
     * @param \Applicazza\Appointed\Period[] ...$periods
     * @return boolean
     */
    public function addOperatingPeriods(Period ...$periods)
    {
        // Make copy of current agenda

        $agenda = clone $this->agenda;

        // Sort periods

        usort($periods, function (Period $a, Period $b) {

            switch (true) {
                case $a->getStartsAt()->eq($b->getStartsAt()):
                    return 0;
                    break;
                case $a->getStartsAt()->lt($b->getStartsAt()):
                    return -1;
                    break;
                case $a->getStartsAt()->gt($b->getStartsAt()):
                    return 1;
                    break;
            }

            return 0;

        });

        // Iterate over periods

        foreach ($periods as $period) {

            $agenda->rewind();

            if ($agenda->count() === 0) {

                $agenda->push($period);

                continue;
            }

            $index = 0;

            while ($agenda->valid()) {

                if ($period->isOverlapping($agenda->current()))
                    return false;

                if ($period->isAfter($agenda->current())) {
                    $index = $agenda->key();
                    break;
                }

                $agenda->next();
            }

            $agenda->add(++$index, $period);

        }

        // If everything is ok update agenda

        $this->agenda = $agenda;

        return true;
    }

    /**
     * @param \Applicazza\Appointed\Appointment[] ...$appointments
     * @return boolean
     */
    public function addAppointments(Appointment ...$appointments)
    {
        // Make copy of current agenda

        $agenda = clone $this->agenda;

        // Iterate over appointments

        foreach ($appointments as $appointment) {

            $is_inserted = false;

            for ($agenda->rewind(); $agenda->valid(); $agenda->next()) {

                /** @var \Applicazza\Appointed\Period $current */
                $current = $agenda->current();

                if ($appointment->isEnclosedBy($current)) {

                    if ($current instanceof Appointment)
                        return false;

                    $periods = $current->split($appointment);

                    $agenda->embed($agenda->key(), $periods);

                    $is_inserted = true;

                    break;
                }
            }

            if (!$is_inserted)
                return false;

        }

        // If everything is ok update agenda

        $this->agenda = $agenda;

        return true;
    }

    /**
     * @param \Applicazza\Appointed\Appointment $appointment
     * @param string $direction 'forward' or 'backward'
     * @return \Applicazza\Appointed\Appointment|null
     */
    public function fit(Appointment $appointment, $direction = 'forward')
    {
        $agenda = clone $this->agenda;

        if ($direction === 'forward')
            $agenda->setIteratorMode(Agenda::IT_MODE_FIFO);
        else
            $agenda->setIteratorMode(Agenda::IT_MODE_LIFO);

        $agenda->rewind();

        while ($agenda->valid()) {

            /** @var \Applicazza\Appointed\Period $current */
            $current = $agenda->current();

            if ($current instanceof Appointment) {
                $agenda->next();
                continue;
            }

            if ($direction === 'forward') {

                if ($current->isLaterThan($appointment) && $current->isEqualOrLonger($appointment)) {
                    return Appointment::makeUsingStartsAtAndDuration($current->getStartsAt(), $appointment->length());
                }

            } else {

                if ($appointment->isLaterThan($current) && $current->isEqualOrLonger($appointment)) {
                    return Appointment::makeUsingEndsAtAndDuration($current->getEndsAt(), $appointment->length());
                }

            }

            $agenda->next();
        }

        return null;
    }

    /**
     * @return \Applicazza\Appointed\Period[]
     */
    public function getAgenda()
    {
        $agenda = [];

        $this->agenda->rewind();

        while ($this->agenda->valid()) {

            $agenda[] = $this->agenda->current();

            $this->agenda->next();
        }

        return $agenda;
    }

    /**
     * @param \Applicazza\Appointed\Period $period
     * @return boolean
     */
    public function deleteOperatingPeriod(Period $period)
    {
        $agenda = clone $this->agenda;

        $is_deleted = false;

        for ($agenda->rewind(); $agenda->valid(); $agenda->next()) {

            /** @var \Applicazza\Appointed\Period $current */
            $current = $agenda->current();

            if ($current->isTheSameAs($period)) {

                if ($current instanceof Appointment)
                    return false;

                $agenda->offsetUnset($agenda->key());

                $is_deleted = true;

                break;
            }
        }

        if (!$is_deleted)
            return false;

        $this->agenda = $agenda;

        return true;
    }

    /**
     * @param \Applicazza\Appointed\Period $old_period
     * @param \Applicazza\Appointed\Period $new_period
     * @return bool
     */
    public function editOperatingPeriod(Period $old_period, Period $new_period)
    {
        if ($this->deleteOperatingPeriod($old_period)) {
            if ($this->addOperatingPeriods($new_period)) {
                return true;
            } else {
                $this->addOperatingPeriods($old_period);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param \Applicazza\Appointed\Appointment[] ...$appointments
     * @return bool
     */
    public function deleteAppointments(Appointment ...$appointments)
    {
        // Make copy of current agenda

        $agenda = clone $this->agenda;

        $is_deleted = true;

        foreach ($appointments as $appointment) {

            $is_deleted = false;

            for ($agenda->rewind(); $agenda->valid(); $agenda->next()) {

                /** @var \Applicazza\Appointed\Period $current */
                $current = $agenda->current();

                if ($current instanceof Appointment && $current->isTheSameAs($appointment)) {

                    $index = $agenda->key();

                    $current = Period::make($current->getStartsAt(), $current->getEndsAt());

                    // check before

                    if ($agenda->offsetExists($index - 1) && !($agenda->offsetGet($index - 1) instanceof Appointment) && $current->isIntersecting($agenda->offsetGet($index - 1))) {
                        // merge and delete
                        $current = $current->merge($agenda->offsetGet($index - 1));
                        $agenda->offsetSet($index, $current);
                        $agenda->offsetUnset($index - 1);
                        $index = $index - 1;

                    } else {
                        $agenda->offsetSet($index, $current);
                    }

                    // check after

                    if ($agenda->offsetExists($index + 1) && !($agenda->offsetGet($index + 1) instanceof Appointment) &&  $current->isIntersecting($agenda->offsetGet($index + 1))) {
                        // merge and delete
                        $current = $current->merge($agenda->offsetGet($index + 1));
                        $agenda->offsetSet($index, $current);
                        $agenda->offsetUnset($index + 1);
                    } else {
                        $agenda->offsetSet($index, $current);
                    }

                    $is_deleted = true;

                    break;
                }
            }
        }

        if (!$is_deleted)
            return false;

        // If everything is ok update agenda

        $this->agenda = $agenda;

        return true;
    }
}