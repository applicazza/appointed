<?php

namespace Applicazza\Appointed\Common;

use Applicazza\Appointed\Period;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use InvalidArgumentException;

/**
 * Interface IPeriod
 * @package Applicazza\Appointed\Common
 */
interface IPeriod
{
    /**
     * @param \Carbon\Carbon $starts_at
     * @param \Carbon\Carbon $ends_at
     * @return static
     */
    public static function make(Carbon $starts_at, Carbon $ends_at);

    /**
     * @param \Carbon\Carbon $starts_at
     * @param \Carbon\CarbonInterval $duration
     * @return static
     */
    public static function makeUsingStartsAtAndDuration(Carbon $starts_at, CarbonInterval $duration);

    /**
     * @param \Carbon\Carbon $ends_at
     * @param \Carbon\CarbonInterval $duration
     * @return static
     */
    public static function makeUsingEndsAtAndDuration(Carbon $ends_at, CarbonInterval $duration);

    /**
     * @return \Carbon\Carbon
     */
    public function getStartsAt();

    /**
     * @return \Carbon\Carbon
     */
    public function getEndsAt();

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isAfter(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isBefore(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isEnclosing(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isEndTouching(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isIntersecting(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isOverlapping(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isStartTouching(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isTheSameAs(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isEnclosedBy(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return \Applicazza\Appointed\Period[]
     */
    public function split(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isEqualOrLonger(IPeriod $period);

    /**
     * @return \Carbon\CarbonInterval
     */
    public function length();

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return bool
     */
    public function isLaterThan(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return \Applicazza\Appointed\Period|\Applicazza\Appointed\Appointment|bool
     */
    public function merge(IPeriod $period);
}