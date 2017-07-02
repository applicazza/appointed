<?php

namespace Applicazza\Appointed\Tests\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * Class CarbonHelper
 * @package Applicazza\Appointed\Tests\Helpers
 */
class CarbonHelper
{
    /**
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return Carbon
     */
    public static function today($hours = 0, $minutes = 0, $seconds = 0)
    {
        return Carbon::today()->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);
    }

    /**
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return CarbonInterval
     */
    public static function interval($hours = 0, $minutes = 0, $seconds = 0)
    {
        return CarbonInterval::create(0, 0, 0, 0, $hours, $minutes, $seconds);
    }
}