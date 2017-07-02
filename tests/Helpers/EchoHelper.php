<?php

namespace Applicazza\Appointed\Tests\Helpers;

use Carbon\Carbon;

/**
 * Class EchoHelper
 * @package Applicazza\Appointed\Tests\Helpers
 */
class EchoHelper
{
    /**
     * @param array ...$messages
     */
    public static function info(... $messages)
    {
        $output = '';

        foreach ($messages as $message) {
            if ($message instanceof Carbon)
                $message = $message->toRfc3339String();

            $output = sprintf('%s %s', $output, $message);
        }

        $output = trim($output);

        echo $output, PHP_EOL;
    }
}