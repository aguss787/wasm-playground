<?php

namespace App\Tesseract;

use App\Tesseract\Wasm\Struct\Graph;

class ShortestPathReturn
{
    private Graph $graph;
    private int $totalCost;
    private array $path;
    private array $costs;

    /**
     * ShortestPath constructor.
     * @param Graph $graph
     * @param int $totalCost
     * @param int[] $path
     * @param int[] $costs
     */
    public function __construct(Graph $graph, int $totalCost, array $path, array $costs)
    {
        $this->graph = $graph;
        $this->totalCost = $totalCost;
        $this->path = $path;
        $this->costs = $costs;
    }

    /**
     * @return Graph
     */
    public function getGraph(): Graph
    {
        return $this->graph;
    }

    /**
     * @return int
     */
    public function getTotalCost(): int
    {
        return $this->totalCost;
    }

    /**
     * @return int[]
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @return int[]
     */
    public function getCosts(): array
    {
        return $this->costs;
    }
}
