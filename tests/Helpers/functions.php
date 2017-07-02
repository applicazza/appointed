<?php

use Applicazza\Appointed\Tests\Helpers;

if (!function_exists('today')) {

    /**
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return \Carbon\Carbon
     */
    function today($hours = 0, $minutes = 0, $seconds = 0)
    {
        return Helpers\CarbonHelper::today($hours, $minutes, $seconds);
    }

}

if (!function_exists('interval')) {

    /**
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return \Carbon\CarbonInterval
     */
    function interval($hours = 0, $minutes = 0, $seconds = 0)
    {
        return Helpers\CarbonHelper::interval($hours, $minutes, $seconds);
    }

}

if (!function_exists('info')) {

    /**
     * @param array ...$messages
     */
    function info(... $messages) {
        Helpers\EchoHelper::info(... $messages);
    }

}