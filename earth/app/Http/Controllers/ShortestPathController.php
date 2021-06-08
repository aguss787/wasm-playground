<?php


namespace App\Http\Controllers;


use App\Tesseract\TesseractController;
use App\Tesseract\Wasm\Struct\Graph;
use App\Tesseract\Wasm\Struct\Path;

class ShortestPathController extends Controller
{
    /**
     * Run the wasm example.
     */
    public function run()
    {
        $timer = new Timer();
        $timer->run(function () {
            $tesseract = new TesseractController('../../tesseract/target/wasm32-unknown-unknown/release/tesseract.wasm');
            $result = $tesseract->shortest_path(new Graph([
                new Path(1, 2, 4),
                new Path(1, 3, 2),
                new Path(2, 3, 5),
                new Path(2, 4, 10),
                new Path(3, 5, 3),
                new Path(4, 6, 11),
                new Path(5, 4, 4),
            ]),1, 6);

            echo '<img width="250px" src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3b/Shortest_path_with_direct_weights.svg/1200px-Shortest_path_with_direct_weights.svg.png" />';
            echo '<br />';

            echo 'Shortest path cost: '.$result->getTotalCost().'<br />';
            echo 'Path:<br/>';

            echo $result->getPath()[0];
            for($i = 1; $i < count($result->getPath()); $i++) {
                echo '---('.$result->getCosts()[$i].')-->';
                echo $result->getPath()[$i];
            }
            echo '<br />';
        });
    }
}
