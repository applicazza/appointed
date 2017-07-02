<?php

namespace Applicazza\Appointed\Common;

/**
 * Class Period
 * @package Applicazza\Appointed
 */
interface IPeriod
{
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
     * @return boolean
     */
    public function includes(IPeriod $period);

    /**
     * @return \Carbon\CarbonInterval
     */
    public function length();

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return boolean
     */
    public function startsAfter(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return boolean
     */
    public function endsBefore(IPeriod $period);

    /**
     * @param \Applicazza\Appointed\Common\IPeriod $period
     * @return boolean
     */
    public function canFit(IPeriod $period);
}