<?php


namespace App\Tesseract;

use Wasm;

class WasmController
{
    protected Wasm\Instance $instance;
    protected Wasm\MemoryView $memory_view;
    private Wasm\Func $_malloc;
    private Wasm\Func $_free;

    public function __construct($wasm_file) {
        // Create an Engine
        $engine = Wasm\Engine::new();

        // Create a Store
        $store = Wasm\Store::new($engine);

        // Wasm file
        $wasmBytes = file_get_contents($wasm_file);

        $module = Wasm\Module::new($store, $wasmBytes);
        $this->instance = Wasm\Instance::new($store, $module);

        $exports = $this->instance->exports();
        $memory = wasm_extern_as_memory($exports[0]);
        $this->memory_view = wasm_memory_data($memory);

        $length = count($exports);
        $this->_free = (new Wasm\Extern($exports[$length - 4]))->asFunc();
        $this->_malloc = (new Wasm\Extern($exports[$length - 3]))->asFunc();
    }

    private function malloc($bytes) {
        $firstArg = Wasm\Val::newI32($bytes);
        $args = new Wasm\Vec\Val([$firstArg->inner()]);
        $result = ($this->_malloc)($args);
        return (new Wasm\Val($result[0]))->value();
    }

    private function free($ptr, $size) {
        $firstArg = Wasm\Val::newI32($ptr);
        $secondArg = Wasm\Val::newI32($size);
        $args = new Wasm\Vec\Val([$firstArg->inner(), $secondArg->inner()]);
        ($this->_free)($args);
    }

    protected function allocate_i32_array($vals) {
        $length = count($vals);

        $address = $this->malloc(16);
        $array_begin = $this->malloc($length * 4);

        // Set address
        $this->memory_view->setI32($address, $array_begin);
        $this->memory_view->setI32($address + 8, $length);

        for ($i = 0 ; $i < $length ; $i++) {
            $this->memory_view->setI32($array_begin + $i * 4, $vals[$i]);
        }

        return $address;
    }

    protected function deallocate_i32_array($ptr) {
        $array_begin = $this->memory_view->getI32($ptr);
        $length = $this->memory_view->getI32($ptr + 8);

        $this->free($ptr, 16);
        $this->free($array_begin, $length);
    }
}
