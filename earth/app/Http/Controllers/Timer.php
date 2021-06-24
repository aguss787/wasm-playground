<?php


namespace App\Http\Controllers;


use Closure;

class Timer
{
    /**
     * @var float
     */
    private $startTime;

    public function start() {
        $this->startTime = microtime(true);
    }

    public function stop() {
        return microtime(true) - $this->startTime;
    }

    public function run(Closure $param)
    {
        $startTime = microtime(true);

        $param();

        $stopTime = microtime(true) - $startTime;
        echo 'Execution time: '.number_format((float) $stopTime, 10).'ms.<br />';
    }

    public function runReturn(Closure $param)
    {
        $startTime = microtime(true);

        $param();

        return microtime(true) - $startTime;
    }
}
