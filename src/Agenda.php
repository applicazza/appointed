<?php

namespace Applicazza\Appointed;

use SplDoublyLinkedList;

/**
 * Class Agenda
 * @package Applicazza\Appointed
 */
class Agenda extends SplDoublyLinkedList
{
    /**
     * @param $key
     * @param $items
     */
    public function embed($key, $items)
    {
        $this->offsetSet($key, $items[0]);

        for ($i = 1; $i < count($items); $i++) {
            $this->add($key + $i, $items[$i]);
        }
    }
}