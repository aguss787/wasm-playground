<?php


namespace App\Http\Controllers;

use App\Tesseract\TesseractController;

class SummerController extends Controller
{
    /**
     * Run the wasm example.
     */
    public function run()
    {
        $timer = new Timer();
        $timer->run(function () {
            $tesseract = new TesseractController('../../tesseract/target/wasm32-unknown-unknown/release/tesseract.wasm');
            $result = $tesseract->sum([1, 20, 300, 4000]);

            echo 'Results of `sum`: '.$result.'<br />';
        });
    }
}
