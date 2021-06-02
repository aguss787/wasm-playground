<?php


namespace App\Tesseract\Wasm\Struct;


use Wasm;

class Path extends WasmStruct
{
    private int $from;
    private int $to;
    private int $cost;

    /**
     * Path constructor.
     * @param int $from
     * @param int $to
     * @param int $cost
     */
    public function __construct(int $from, int $to, int $cost)
    {
        $this->from = $from;
        $this->to = $to;
        $this->cost = $cost;
    }

    public function allocate_self(Wasm\MemoryView $memoryView, callable $malloc, $addr)
    {
        $memoryView->setI32($addr + 0, $this->from);
        $memoryView->setI32($addr + 4, $this->to);
        $memoryView->setI32($addr + 8, $this->cost);
    }

    public static function deallocate_child(Wasm\MemoryView $memoryView, callable $free, $addr)
    {
    }

    public static function size()
    {
        return 12;
    }
}
