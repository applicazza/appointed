<?php

namespace Applicazza\Appointed;

use Applicazza\Appointed\Common\IPeriod;
use Applicazza\Appointed\Traits\PeriodAware;
use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * Class Period
 * @package Applicazza\Appointed
 */
class Period implements IPeriod
{
    use PeriodAware;

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