<?php

namespace Applicazza\Appointed;

use InvalidArgumentException;

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
     * @return static
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
                    throw new InvalidArgumentException;

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

        return $this;
    }

    /**
     * @param \Applicazza\Appointed\Appointment[] ...$appointments
     * @return $this
     */
    public function addAppointments(Appointment ...$appointments)
    {
        // Make copy of current agenda

        $agenda = clone $this->agenda;

        // Iterate over appointments

        foreach ($appointments as $appointment) {

            $agenda->rewind();

            while ($agenda->valid()) {

                /** @var \Applicazza\Appointed\Period $current */
                $current = $agenda->current();

                if ($appointment->isOverlapping($current)) {

                    if ($current instanceof Appointment)
                        throw new InvalidArgumentException;

                    $periods = $current->split($appointment);

                    $agenda->embed($agenda->key(), $periods);

                    break;
                }

                $agenda->next();
            }

        }

        // If everything is ok update agenda

        $this->agenda = $agenda;

        return $this;
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

                if ($current->isAfter($appointment) && $current->isEqualOrLonger($appointment)) {
                    return Appointment::makeUsingStartsAtAndDuration($current->getStartsAt(), $appointment->length());
                }

            } else {

                if ($appointment->isAfter($current) && $current->isEqualOrLonger($appointment)) {
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

        for ($agenda->rewind(); $agenda->valid(); $agenda->next()) {

            /** @var \Applicazza\Appointed\Period $current */
            $current = $agenda->current();

            if ($current->isEnclosing($current) || $current->isTheSameAs($period)) {

                if ($current instanceof Appointment)
                    return false;

                $agenda->offsetUnset($agenda->key());
                break;
            }

            if ($current->isOverlapping($period))
                return false;
        }

        $this->agenda = $agenda;

        return true;
    }
}