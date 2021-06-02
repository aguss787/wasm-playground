<?php


namespace App\Http\Controllers;


use App\Tesseract\TesseractController;
use App\Tesseract\Wasm\Struct\Graph;
use App\Tesseract\Wasm\Struct\Path;

class PathSumCostController extends Controller
{
    /**
     * Run the wasm example.
     */
    public function run()
    {
        $timer = new Timer();
        $timer->run(function () {
            $tesseract = new TesseractController('../../tesseract/target/wasm32-unknown-unknown/release/tesseract.wasm');
            $result = $tesseract->total_path_cost(new Graph([
                new Path(1, 1, 4),
                new Path(1, 1, 50),
                new Path(1, 1, 600),
            ]));

            echo 'Results of `total_path_cost`: '.$result.'<br />';
        });
    }
}
