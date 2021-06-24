<?php

namespace App\Http\Controllers\Graph;

class DisplayableNode
{
    public string $label;
    public int $x;
    public int $y;

    /**
     * DisplayableNode constructor.
     * @param string $label
     * @param int $x
     * @param int $y
     */
    public function __construct(string $label, int $x, int $y)
    {
        $this->label = $label;
        $this->x = $x;
        $this->y = $y;
    }
}
