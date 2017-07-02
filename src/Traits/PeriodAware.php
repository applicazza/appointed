<?php

namespace Applicazza\Appointed\Traits;

use Applicazza\Appointed\Common\IPeriod;
use Carbon\Carbon;
use Carbon\CarbonInterval;

trait PeriodAware
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
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return boolean
     */
    public function includes(IPeriod $period)
    {
        return $period->getStartsAt()->gte($this->getStartsAt()) && $this->getEndsAt()->gte($period->getEndsAt());
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
     * @return boolean
     */
    public function startsAfter(IPeriod $period)
    {
        return $this->getStartsAt()->gte($period->getStartsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return boolean
     */
    public function endsBefore(IPeriod $period)
    {
        return $this->getEndsAt()->lte($period->getEndsAt());
    }

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return boolean
     */
    public function canFit(IPeriod $period)
    {
        return $this->length()->seconds >= $period->length()->seconds;
    }
}