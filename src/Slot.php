<?php

namespace Applicazza\Appointed;

use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * Class Slot
 * @package Applicazza\Appointed
 */
class Slot
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
     * AppointmentSlot constructor.
     * @param \Carbon\Carbon $starts_at
     * @param \Carbon\Carbon $ends_at
     */
    public function __construct(Carbon $starts_at, Carbon $ends_at)
    {
        $this->starts_at = $starts_at;
        $this->ends_at = $ends_at;
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
     * @return CarbonInterval
     */
    public function getDuration()
    {
        return CarbonInterval::seconds($this->getEndsAt()->diffInSeconds($this->getStartsAt()));
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
}