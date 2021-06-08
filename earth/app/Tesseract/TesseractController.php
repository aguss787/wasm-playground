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
    private Wasm\Func $_shortest_path;

    public function __construct($wasmFile)
    {
        parent::__construct($wasmFile);

        $exports = $this->instance->exports();
        $this->_add_one = (new Wasm\Extern($exports[1]))->asFunc();
        $this->_sum = (new Wasm\Extern($exports[2]))->asFunc();
        $this->_array_length = (new Wasm\Extern($exports[3]))->asFunc();
        $this->_total_path_cost = (new Wasm\Extern($exports[4]))->asFunc();
        $this->_shortest_path = (new Wasm\Extern($exports[5]))->asFunc();
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

    /**
     * @param Graph $graph
     * @param int $from
     * @param int $finish
     * @return ShortestPathReturn
     */
    public function shortest_path(Graph $graph, int $from, int $finish) {
        $address = $graph->allocate($this->memoryView, $this->malloc);
        $returnAddress = ($this->malloc)(24);

        $returnAddressArgs = Wasm\Val::newI32($returnAddress);
        $firstArg = Wasm\Val::newI32($address);
        $secondArg = Wasm\Val::newI32($from);
        $thirdArg = Wasm\Val::newI32($finish);
        $args = new Wasm\Vec\Val([$returnAddressArgs->inner(), $firstArg->inner(), $secondArg->inner(), $thirdArg->inner()]);
        ($this->_shortest_path)($args);

        Graph::deallocate($this->memoryView, $this->free, $address);

        $totalCost = $this->memoryView->getI32($returnAddress + 0);
        $arrayAddr = $this->memoryView->getI32($returnAddress + 4);
        $arrayLength = $this->memoryView->getI32($returnAddress + 8);
        $path = [];
        $costs = [];
        for($i = 0; $i < $arrayLength; $i ++) {
            array_push($path, $this->memoryView->getI32($arrayAddr + $i * 8 + 0));
            array_push($costs, $this->memoryView->getI32($arrayAddr + $i * 8 + 4));
        }

        return new ShortestPathReturn(
            $graph,
            $totalCost,
            $path,
            $costs,
        );
    }
}
