<?php

namespace Applicazza\Appointed;

use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * @param int $hours
 * @param int $minutes
 * @param int $seconds
 * @return \Carbon\CarbonInterval
 */
function interval($hours = 0, $minutes = 0, $seconds = 0)
{
    return CarbonInterval::create(0, 0, 0, 0, $hours, $minutes, $seconds);
}

/**
 * @param int $hours
 * @param int $minutes
 * @param int $seconds
 * @return \Carbon\Carbon
 */
function today($hours = 0, $minutes = 0, $seconds = 0)
{
    return Carbon::today()->add(interval($hours, $minutes, $seconds));
}