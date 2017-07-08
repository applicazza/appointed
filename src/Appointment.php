<?php

namespace Applicazza\Appointed;

class Appointment extends Period
{
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
            'status' => 'busy',
        ];
    }
}