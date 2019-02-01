<?php

namespace builduhc\math;

class Time {

    /**
     * @param int $time
     * @return string
     */
    public static function calculateTime(int $time): string {
        return gmdate("i:s", $time); 
    }
}
