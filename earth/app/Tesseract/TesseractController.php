<?php /** @noinspection PhpIllegalArrayKeyTypeInspection */


namespace App\Tesseract;

use Wasm;

class TesseractController extends WasmController
{
    private Wasm\Func $_add_one;
    private Wasm\Func $_sum;
    private Wasm\Func $_array_length;

    public function __construct($wasm_file)
    {
        parent::__construct($wasm_file);

        $exports = $this->instance->exports();
        $this->_add_one = (new Wasm\Extern($exports[1]))->asFunc();
        $this->_sum = (new Wasm\Extern($exports[2]))->asFunc();
        $this->_array_length = (new Wasm\Extern($exports[3]))->asFunc();
    }

    public function add_one($val) {
        $firstArg = Wasm\Val::newI32($val);
        $args = new Wasm\Vec\Val([$firstArg->inner()]);
        $result = ($this->_add_one)($args);
        return (new Wasm\Val($result[0]))->value();
    }

    public function sum($vals) {
        $address = $this->allocate_i32_array($vals);

        $firstArg = Wasm\Val::newI32($address);
        $args = new Wasm\Vec\Val([$firstArg->inner()]);
        $result = ($this->_sum)($args);

        $this->deallocate_i32_array($address);
        return (new Wasm\Val($result[0]))->value();
    }
}
