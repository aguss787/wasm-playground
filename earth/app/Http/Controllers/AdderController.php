<?php


namespace App\Http\Controllers;

use Wasm;

class AdderController extends Controller
{
    /**
     * Run the wasm example.
     */
    public function run()
    {
        $timer = new Timer();

        $timer->start();
        // Create an Engine
        $engine = Wasm\Engine::new();

        // Create a Store
        $store = Wasm\Store::new($engine);

        // Wasm file
        $wasmBytes = file_get_contents('../../tesseract/target/wasm32-unknown-unknown/release/tesseract.wasm');

        echo 'Compiling module...<br />';
        $module = Wasm\Module::new($store, $wasmBytes);

        echo 'Instantiating module...<br />';
        $instance = Wasm\Instance::new($store, $module);

        // Extracting export...
        $exports = $instance->exports();
        $sum = (new Wasm\Extern($exports[1]))->asFunc();

        $firstArg = Wasm\Val::newI32(1);
        $args = new Wasm\Vec\Val([$firstArg->inner()]);

        echo 'Calling `sum` function...<br />';
        $result = $sum($args);

        echo 'Results of `sum`: '.((new Wasm\Val($result[0]))->value()).'<br />';
        echo 'Execution time: '.number_format((float) $timer->stop(), 10).'ms.<br />';
    }
}
