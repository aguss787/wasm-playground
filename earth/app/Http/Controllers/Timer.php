<?php


namespace App\Http\Controllers;


use Closure;

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

    public function run(Closure $param)
    {
        $start_time = microtime(true);

        $param();

        $stop_time = microtime(true) - $start_time;
        echo 'Execution time: '.number_format((float) $stop_time, 10).'ms.<br />';
    }
}
