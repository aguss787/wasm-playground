<?php /** @noinspection PhpIllegalArrayKeyTypeInspection */


namespace App\Tesseract;

use App\Tesseract\Wasm\Struct\Graph;
use Wasm;

class TesseractController extends WasmController
{
    private Wasm\Func $_add_one;
    private Wasm\Func $_sum;
    private Wasm\Func $_array_length;
    private Wasm\Func $_total_path_cost;

    public function __construct($wasmFile)
    {
        parent::__construct($wasmFile);

        $exports = $this->instance->exports();
        $this->_add_one = (new Wasm\Extern($exports[1]))->asFunc();
        $this->_sum = (new Wasm\Extern($exports[2]))->asFunc();
        $this->_array_length = (new Wasm\Extern($exports[3]))->asFunc();
        $this->_total_path_cost = (new Wasm\Extern($exports[4]))->asFunc();
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

    /**
     * @param Graph $graph
     * @return float|int
     */
    public function total_path_cost(Graph $graph) {
        $address = $graph->allocate($this->memoryView, $this->malloc);

        $firstArg = Wasm\Val::newI32($address);
        $args = new Wasm\Vec\Val([$firstArg->inner()]);
        $result = ($this->_total_path_cost)($args);

        Graph::deallocate($this->memoryView, $this->free, $address);
        return (new Wasm\Val($result[0]))->value();
    }
}
