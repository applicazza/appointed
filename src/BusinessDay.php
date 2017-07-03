<?php

namespace Applicazza\Appointed;

use Carbon\Carbon;
use InvalidArgumentException;
use SplDoublyLinkedList;

/**
 * Class BusinessDay
 * @package Applicazza\Appointed
 */
class BusinessDay
{
    /**
     * @var \Applicazza\Appointed\Period[]
     */
    protected $business_hours = [];

    /**
     * @var \Applicazza\Appointed\Period[]
     */
    protected $occupied_slots = [];

    /**
     * @var \SplDoublyLinkedList
     */
    protected $slots;

    /**
     * BusinessDay constructor.
     */
    function __construct()
    {
        $this->slots = new SplDoublyLinkedList;
    }

    /**
     * @param array ...$business_hours
     * @return static
     */
    public function addBusinessHours(... $business_hours)
    {
        $pending_business_hours = [];

        foreach ($business_hours as $business_hour_period) {

            if (!is_object($business_hour_period) || get_class($business_hour_period) !== Period::class)
                throw new InvalidArgumentException();

            $pending_business_hours[] = $business_hour_period;
        }

        if (count($pending_business_hours)) {

            $this->business_hours = array_merge($this->business_hours, $pending_business_hours);

            $this->sortBusinessHours();

            $this->rebuild();

        }

        return $this;
    }

    /**
     * @return \Applicazza\Appointed\Period[]
     */
    public function getBusinessHours()
    {
        return $this->business_hours;
    }

    /**
     * @return \Applicazza\Appointed\Period[]
     */
    public function getOccupiedSlots()
    {
        return $this->occupied_slots;
    }

    /**
     * @param array ...$occupied_slots
     * @return static
     */
    public function addOccupiedSlots(... $occupied_slots)
    {
        $pending_occupied_slots = [];

        foreach ($occupied_slots as $occupied_slot) {

            if (!is_object($occupied_slot) || get_class($occupied_slot) !== Period::class)
                throw new InvalidArgumentException();

            $pending_occupied_slots[] = $occupied_slot;
        }

        if (count($pending_occupied_slots)) {

            $this->occupied_slots = array_merge($this->occupied_slots, $pending_occupied_slots);

            $this->sortOccupiedSlots();

            $this->rebuild();

        }

        return $this;
    }

    /**
     * @param \Applicazza\Appointed\Period $period
     * @return bool
     */
    public function isAvailableAt(Period $period)
    {
        $this->getSlots()->rewind();

        while ($this->getSlots()->valid()) {

            /** @var \Applicazza\Appointed\Period $slot */
            $slot = $this->getSlots()->current();

            if ($slot->includes($period))
                return true;

            $this->getSlots()->next();
        }

        return false;
    }

    /**
     * @param \Applicazza\Appointed\Period $period
     * @return bool
     */
    public function isNotAvailableAt(Period $period)
    {
        return !$this->isAvailableAt($period);
    }

    /**
     * @param \Applicazza\Appointed\Period $period
     * @param bool $before
     * @return \Applicazza\Appointed\Period|bool
     */
    public function closestFor(Period $period, $before = false)
    {
        $slots = $this->getSlots();

        if ($before)
            $slots->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);

        $slots->rewind();

        while ($slots->valid()) {

            /** @var \Applicazza\Appointed\Period $slot */
            $slot = $slots->current();

            if ($before) {

                if ($period->startsAfter($slot) && $slot->canFit($period)) {
                    return new Period((new Carbon($slot->getEndsAt()))->sub($period->length()), $slot->getEndsAt());
                }

            } else {

                if ($slot->startsAfter($period) && $slot->canFit($period)) {
                    return Period::make($slot->getStartsAt(), $period->length());
                }
            }

            $slots->next();
        }

        return false;
    }

    /**
     * @return \SplDoublyLinkedList
     */
    protected function getSlots()
    {
        return $this->slots;
    }

    /**
     *  Sorts business hours
     */
    protected function sortBusinessHours()
    {
        usort($this->business_hours, function ($a, $b) {
            if ($a == $b) return 0;
            return $a < $b ? -1 : 1;
        });
    }

    /**
     *  Sorts business hours
     */
    protected function sortOccupiedSlots()
    {
        usort($this->occupied_slots, function ($a, $b) {
            if ($a == $b) return 0;
            return $a < $b ? -1 : 1;
        });
    }

    /**
     *  Rebuild internal representation
     */
    protected function rebuild()
    {
        $slots = new SplDoublyLinkedList;

        foreach ($this->getBusinessHours() as $period) {
            $slots->push($period);
        }

        foreach ($this->getOccupiedSlots() as $period) {

            $slots->rewind();

            while ($slots->valid()) {

                /** @var \Applicazza\Appointed\Period $slot */
                $slot = $slots->current();

                if ($slot->includes($period)) {

                    $index = $slots->key();

                    $slots->offsetUnset($index);

                    if ($slot->getStartsAt()->notEqualTo($period->getStartsAt()))
                        $slots->add($index++, new Period($slot->getStartsAt(), $period->getStartsAt()));

                    $slots->add($index, new Period($period->getEndsAt(), $slot->getEndsAt()));

                }

                $slots->next();

            }
        }

        $this->slots = $slots;
    }
}