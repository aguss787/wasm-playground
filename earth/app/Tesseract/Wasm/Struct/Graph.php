<?php


namespace App\Tesseract\Wasm\Struct;


use Wasm;

class Graph extends WasmStruct
{
    private array $paths;

    /**
     * Graph constructor.
     * @param Path[] $paths
     */
    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function allocate_self(Wasm\MemoryView $memoryView, callable $malloc, $addr)
    {
        $length = count($this->paths);
        $size = $length * Path::size();
        $arrayAddr = $malloc($size);

        $memoryView->setI32($addr, $arrayAddr);
        $memoryView->setI32($addr + 8, $length);

        for ($i = 0; $i < $length; $i++) {
            $this->paths[$i]->allocate_self($memoryView, $malloc, $arrayAddr + $i * Path::size());
        }
    }

    public static function deallocate_child(Wasm\MemoryView $memoryView, callable $free, $addr)
    {
        $arrayAddr = $memoryView->getI32($addr);
        $length = $memoryView->getI32($addr + 8);
        $size = $length * Path::size();

        for ($i = 0; $i < $length; $i++) {
            Path::deallocate_child($memoryView, $free, $arrayAddr + $i * Path::size());
        }

        $free($addr, $size);
    }

    public static function size()
    {
        return 16;
    }

    /**
     * @return Path[]
     */
    public function getPaths(): array
    {
        return $this->paths;
    }
}
