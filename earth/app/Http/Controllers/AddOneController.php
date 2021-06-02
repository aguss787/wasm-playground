<?php


namespace App\Http\Controllers;

use App\Tesseract\TesseractController;

class AddOneController extends Controller
{
    /**
     * Run the wasm example.
     */
    public function run()
    {
        $timer = new Timer();
        $timer->run(function () {
            $tesseract = new TesseractController('../../tesseract/target/wasm32-unknown-unknown/release/tesseract.wasm');
            $result = $tesseract->add_one(786);

            echo 'Results of `add_one`: '.$result.'<br />';
        });
    }
}
