<?php

namespace Applicazza\Appointed;

use Applicazza\Appointed\Common\IPeriod;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class Period
 * @package Applicazza\Appointed
 */
class Period implements IPeriod, JsonSerializable
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
     * Period constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param \Carbon\Carbon $starts_at
     * @param \Carbon\Carbon $ends_at
     * @return static
     */
    public static function make(Carbon $starts_at, Carbon $ends_at)
    {
        if (!$starts_at || !$ends_at || $ends_at->lte($starts_at))
            throw new InvalidArgumentException;

        $instance = new static;
        $instance->starts_at = $starts_at;
        $instance->ends_at = $ends_at;

        return $instance;
    }

    /**
     * @param \Carbon\Carbon $starts_at
     * @param \Carbon\CarbonInterval $duration
     * @return static
     */
    public static function makeUsingStartsAtAndDuration(Carbon $starts_at, CarbonInterval $duration)
    {
        $ends_at = $starts_at->copy()->add($duration);

        if (!$starts_at || !$ends_at || $ends_at->lte($starts_at))
            throw new InvalidArgumentException;

        $instance = new static;
        $instance->starts_at = $starts_at;
        $instance->ends_at = $ends_at;

        return $instance;
    }

    /**
     * @param \Carbon\Carbon $ends_at
     * @param \Carbon\CarbonInterval $duration
     * @return static
     */
    public static function makeUsingEndsAtAndDuration(Carbon $ends_at, CarbonInterval $duration)
    {
        $starts_at = $ends_at->copy()->sub($duration);

        if (!$starts_at || !$ends_at || $ends_at->lte($starts_at))
            throw new InvalidArgumentException;

        $instance = new static;
        $instance->starts_at = $starts_at;
        $instance->ends_at = $ends_at;

        return $instance;
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
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isAfter(IPeriod $period)
    {
        return $this->getStartsAt()->gte($period->getEndsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isBefore(IPeriod $period)
    {
        return $this->getEndsAt()->lt($period->getStartsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isEnclosedBy(IPeriod $period)
    {
        return $this->getStartsAt()->gte($period->getStartsAt()) && $this->getEndsAt()->lte($period->getEndsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isEnclosing(IPeriod $period)
    {
        return $period->getStartsAt()->gt($this->getStartsAt()) && $period->getEndsAt()->lt($this->getEndsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isEqualOrLonger(IPeriod $period)
    {
        return $this->getEndsAt()->diffInSeconds($this->getStartsAt()) >= $period->getEndsAt()->diffInSeconds($period->getStartsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isEndTouching(IPeriod $period)
    {
        return $this->getEndsAt()->eq($period->getStartsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isIntersecting(IPeriod $period)
    {
        return $this->getStartsAt()->lte($period->getEndsAt()) && $period->getStartsAt()->lte($this->getEndsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isLaterThan(IPeriod $period)
    {
        return $this->getStartsAt()->gte($period->getStartsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isOverlapping(IPeriod $period)
    {
        return $this->getStartsAt()->lt($period->getEndsAt()) && $period->getStartsAt()->lt($this->getEndsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isStartTouching(IPeriod $period)
    {
        return $period->getEndsAt()->eq($this->getStartsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isTheSameAs(IPeriod $period)
    {
        return $this->getStartsAt()->eq($period->getStartsAt()) && $this->getEndsAt()->eq($period->getEndsAt());
    }

    /**
     * @return \Carbon\CarbonInterval
     */
    public function length()
    {
        return CarbonInterval::seconds($this->getEndsAt()->diffInSeconds($this->getStartsAt()));
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return \Applicazza\Appointed\Period[]
     */
    public function split(IPeriod $period)
    {
        $periods = [];

        if ($this->getStartsAt()->lt($period->getStartsAt())) {
            $periods[] = Period::make($this->getStartsAt(), $period->getStartsAt());
        }

        $periods[] = $period;

        if ($this->getEndsAt()->gt($period->getEndsAt())) {
            $periods[] = Period::make($period->getEndsAt(), $this->getEndsAt());
        }

        return $periods;
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return \Applicazza\Appointed\Period|\Applicazza\Appointed\Appointment|bool
     */
    public function merge(IPeriod $period)
    {
        if (!$this->isIntersecting($period))
            return false;

        return static::make($this->getStartsAt()->min($period->getStartsAt()), $this->getEndsAt()->max($period->getEndsAt()));
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'starts_at' => $this->getStartsAt()->toRfc3339String(),
            'ends_at' => $this->getEndsAt()->toRfc3339String(),
            'status' => 'available',
        ];
    }
}