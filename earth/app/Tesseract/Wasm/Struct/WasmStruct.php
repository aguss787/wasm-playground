<?php


namespace App\Tesseract\Wasm\Struct;

use Wasm;

abstract class WasmStruct
{
    public abstract function allocate_self(Wasm\MemoryView $memoryView, callable $malloc, $addr);
    public abstract static function deallocate_child(Wasm\MemoryView $memoryView, callable $free, $addr);
    public abstract static function size();

    public function allocate(Wasm\MemoryView $memoryView, callable $malloc): int {
        $addr = $malloc(static::size());
        $this->allocate_self($memoryView, $malloc, $addr);

        return $addr;
    }

    public static function deallocate(Wasm\MemoryView $memoryView, callable $free, $addr) {
        static::deallocate_child($memoryView, $free, $addr);
        $free($addr, static::size());
    }
}
