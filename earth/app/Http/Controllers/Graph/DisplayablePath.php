<?php

namespace App\Http\Controllers\Graph;

use App\Tesseract\Wasm\Struct\Path;

class DisplayablePath extends Path
{
    public string $from;
    public string $to;
    public int $cost;

    /**
     * DisplayablePath constructor.
     * @param int $from
     * @param int $to
     * @param int $cost
     */
    public function __construct(int $from, int $to, int $cost)
    {
        parent::__construct($from, $to, $cost);
        $this->from = strval($from);
        $this->to = strval($to);
        $this->cost = $cost;
    }
}
