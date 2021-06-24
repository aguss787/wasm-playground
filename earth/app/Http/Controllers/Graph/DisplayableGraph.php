<?php

namespace App\Http\Controllers\Graph;

use App\Tesseract\Wasm\Struct\Graph;
use App\Tesseract\Wasm\Struct\Path;
use phpDocumentor\Reflection\Types\String_;
use SebastianBergmann\LinesOfCode\NegativeValueException;
use Throwable;

class DisplayableGraph extends Graph
{
    public array $nodes;
    public array $paths;
    public int $start;
    public int $finish;

    /**
     * DisplayableGraph constructor.
     * @param DisplayableNode[] $nodes
     * @param DisplayablePath[] $paths
     * @param int $start
     * @param int $finish
     */
    public function __construct(array $nodes, array $paths, int $start, int $finish)
    {
        parent::__construct($paths);
        $this->nodes = $nodes;
        $this->paths = $paths;
        $this->start = $start;
        $this->finish = $finish;
    }

    public static function Parse(string $input)
    {
        $reader = new TokenReader($input);

        $n = $reader->readAsInt();
        $m = $reader->readAsInt();
        $start = $reader->readAsInt();
        $finish = $reader->readAsInt();

        $nodes = [];
        for($i = 1; $i <= $n; $i++) {
            array_push($nodes, new DisplayableNode(strval($i), $reader->readAsInt(), $reader->readAsInt()));
        }

        $paths = [];
        for($i = 1; $i <= $m; $i++) {
            $path = new DisplayablePath($reader->readAsInt(), $reader->readAsInt(), $reader->readAsInt());
            array_push($paths, $path);

            if ($path->cost < 0) {
               throw new NegativeCostException();
            }
        }

        return new DisplayableGraph($nodes, $paths, $start, $finish);
    }

    public function toInputFormat(): string {
        $nodeCount = count($this->nodes);
        $edgeCount = count($this->paths);

        $nodesStr = "";
        foreach ($this->nodes as $node) {
            $nodesStr = $nodesStr . "$node->x $node->y" . PHP_EOL;
        }

        $edgesStr = "";
        foreach ($this->paths as $path) {
            $edgesStr = $edgesStr . "$path->from $path->to $path->cost" . PHP_EOL;
        }

        return "$nodeCount $edgeCount $this->start $this->finish" . PHP_EOL . "$nodesStr" . "$edgesStr";
    }
}

class TokenReader {
    private array $tokens;
    private int $idx;
    private int $len;

    /**
     * TokenReader constructor.
     * @param string $str
     */
    public function __construct(string $str)
    {
        $this->tokens = preg_split('/\s+/', $str);
        $this->idx = 0;
        $this->len = count($this->tokens);
    }

    public function hasNTokens(int $n) {
        return $this->idx + $n <= $this->len;
    }

    public function read() {
        return $this->readN(1)[0];
    }

    public function readAsInt() {
        return +$this->read();
    }

    public function readN($n) {
        if (!$this->hasNTokens($n)) {
            throw new NotEnoughTokenException();
        }

        $res = array_slice($this->tokens, $this->idx, $n);
        $this->idx += $n;
        return $res;
    }
}

class NotEnoughTokenException extends \Exception {

}

class NegativeCostException extends \Exception {

}
