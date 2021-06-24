<?php


namespace App\Http\Controllers;


use App\Http\Controllers\Graph\DisplayableGraph;
use App\Http\Controllers\Graph\DisplayableNode;
use App\Http\Controllers\Graph\DisplayablePath;
use App\Http\Controllers\Graph\NegativeCostException;
use App\Http\Controllers\Graph\NotEnoughTokenException;
use App\Tesseract\TesseractController;
use App\Tesseract\Wasm\Struct\Path;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;;
use Throwable;

class ShortestPathController extends Controller
{
    /**
     * Run the wasm example.
     * @param Request $request
     * @return Application|Factory|View
     */
    public function run(Request $request)
    {
        $defaultGraph = new DisplayableGraph([
            new DisplayableNode('1', 50, 300),
            new DisplayableNode('2', 150, 150),
            new DisplayableNode('3', 150, 450),
            new DisplayableNode('4', 250, 150),
            new DisplayableNode('5', 250, 450),
            new DisplayableNode('6', 350, 300),
        ], [
            new DisplayablePath(1, 2, 4),
            new DisplayablePath(1, 3, 2),
            new DisplayablePath(2, 3, 5),
            new DisplayablePath(2, 4, 10),
            new DisplayablePath(3, 5, 3),
            new DisplayablePath(4, 6, 11),
            new DisplayablePath(5, 4, 4),
        ], 1, 6);

        $graph = null;
        $error = null;
        try {
            $graph = DisplayableGraph::Parse((is_null($request->input)) ? "1" : $request->input );
        } catch (NegativeCostException $e) {
            $error = "You cannot have negative edge cost";
        } catch (NotEnoughTokenException $_) {

        } finally {
            if($graph == null) {
                $graph = $defaultGraph;
            }
        }

        $timer = new Timer();
        $time = $timer->runReturn(function () use ($graph, &$totalCost, &$path, &$costs) {
            $tesseract = new TesseractController('../../tesseract/target/wasm32-unknown-unknown/release/tesseract.wasm');
            $result = $tesseract->shortest_path($graph,$graph->start, $graph->finish);

            $totalCost = $result->getTotalCost();
            $path = $result->getPath();
            $costs = $result->getCosts();
        });

        return view('content/shortestpath', [
            "totalCost" => $totalCost,
            "time" => $time,
            "graph" => $graph,
            "path" => $path,
            "costs" => $costs,
            "error" => $error,
        ]);
    }
}

function recursive_array_diff($a1, $a2) {
    $r = array();
    foreach ($a1 as $k => $v) {
        if (array_key_exists($k, $a2)) {
            if (is_array($v)) {
                $rad = recursive_array_diff($v, $a2[$k]);
                if (count($rad)) { $r[$k] = $rad; }
            } else {
                if ($v != $a2[$k]) {
                    $r[$k] = $v;
                }
            }
        } else {
            $r[$k] = $v;
        }
    }
    return $r;
}
