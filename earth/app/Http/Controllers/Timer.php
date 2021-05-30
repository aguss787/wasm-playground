<?php


namespace App\Http\Controllers;


class Timer
{
    /**
     * @var float
     */
    private $start_time;

    public function start() {
        $this->start_time = microtime(true);
    }

    public function stop() {
        return microtime(true) - $this->start_time;
    }
}
