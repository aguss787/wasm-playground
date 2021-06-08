<?php


namespace App\Tesseract;

use Closure;
use Wasm;

class WasmController
{
    protected Wasm\Instance $instance;
    protected Wasm\MemoryView $memoryView;
    private Wasm\Func $_malloc;
    private Wasm\Func $_free;
    protected Closure $free;
    protected Closure $malloc;

    public function __construct($wasmFile) {
        // Create an Engine
        $engine = Wasm\Engine::new();

        // Create a Store
        $store = Wasm\Store::new($engine);

        // Wasm file
        $wasmBytes = file_get_contents($wasmFile);

        $module = Wasm\Module::new($store, $wasmBytes);
        $this->instance = Wasm\Instance::new($store, $module);

        $exports = $this->instance->exports();
        $memory = wasm_extern_as_memory($exports[0]);
        $this->memoryView = wasm_memory_data($memory);

        $length = count($exports);
        $this->_free = (new Wasm\Extern($exports[$length - 4]))->asFunc();
        $this->free = function ($ptr, $size) {
            $firstArg = Wasm\Val::newI32($ptr);
            $secondArg = Wasm\Val::newI32($size);
            $args = new Wasm\Vec\Val([$firstArg->inner(), $secondArg->inner()]);
            ($this->_free)($args);
        };

        $this->_malloc = (new Wasm\Extern($exports[$length - 3]))->asFunc();
        $this->malloc = function ($size) {
            $firstArg = Wasm\Val::newI32($size);
            $args = new Wasm\Vec\Val([$firstArg->inner()]);
            $result = ($this->_malloc)($args);
            return (new Wasm\Val($result[0]))->value();
        };
    }

    protected function allocate_i32_array($vals) {
        $length = count($vals);

        $address = ($this->malloc)(16);
        $arrayBegin = ($this->malloc)($length * 4);

        // Set address
        $this->memoryView->setI32($address, $arrayBegin);
        $this->memoryView->setI32($address + 8, $length);

        for ($i = 0 ; $i < $length ; $i++) {
            $this->memoryView->setI32($arrayBegin + $i * 4, $vals[$i]);
        }

        return $address;
    }

    protected function deallocate_i32_array($ptr) {
        $arrayBegin = $this->memoryView->getI32($ptr);
        $length = $this->memoryView->getI32($ptr + 8);

        ($this->free)($ptr, 16);
        ($this->free)($arrayBegin, $length * 4);
    }
}
