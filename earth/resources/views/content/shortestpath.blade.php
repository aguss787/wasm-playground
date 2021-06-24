@extends('container')

@section('content')
    <div id="graph"></div>
    <script>
        let Embedding = window.jsgraphs.Embedding
        let Graph = window.jsgraphs.Graph
        let Vertex = window.jsgraphs.Vertex
        let Point2D = window.jsgraphs.Point2D

        let graph = new Graph();

        var input_graph = @json($graph);

        let vertexIDs = {};
        let edgeIDs = {};

        let vClasses = {};
        let eClasses = {};

        for (const i of input_graph.nodes) {
            vertexIDs[i.label] = graph.createVertex(i.label, {weight: 2});
            vClasses[vertexIDs[i.label]] = [];
        }

        function edgeSerialization(from, to, cost) {
            return "" + from + "#" + to + "#" + cost;
        }

        for (const i of input_graph.paths) {
            edgeIDs[edgeSerialization(i.from, i.to, i.cost)] = graph.createEdge(vertexIDs[i.from], vertexIDs[i.to], {weight: i.cost});
            eClasses[edgeIDs[edgeSerialization(i.from, i.to, i.cost)]] = [];
        }

        let emb = Embedding.forGraph(graph);

        for (const i of input_graph.nodes) {
            emb.setVertexPosition(vertexIDs[i.label], new Point2D(i.x, i.y));
        }

        let path = @json($path);
        let costs = @json($costs);

        for (let i = 1; i < path.length; i ++) {
            let from = path[i-1];
            let to = path[i];
            let cost = costs[i];

            eClasses[edgeIDs[edgeSerialization(from, to, cost)]].push('used');
        }

        for (let i of path) {
            vClasses[vertexIDs["" + i]].push('used');
        }

        vClasses[vertexIDs["" + input_graph.start]].push('start');
        vClasses[vertexIDs["" + input_graph.finish]].push('finish');

        let svg = emb.toSvg(1000, 600, {verticesCss: vClasses, edgesCss: eClasses, drawEdgesAsArcs: true, displayEdgesLabel: false});

        document.getElementById("graph").insertAdjacentHTML('beforeend', svg);
    </script>

    Shortest path cost: {{ $totalCost }} <br/>
    Execution time: {{ $time }}ms <br/>

    <hr/>

    <form>
        <table>
            <tr>
                <td>
                    format:
                    <pre>
[number of node] [number of edges] [starting node] [finishing node]
[for each node]
    [x] [y]
[/]
[for each edges]
    [from] [to] [cost]
[/]
                    </pre>

                    all index is 1-based
                </td>
                <td>
                    @if(!is_null($error))
                    {{$error}}
                    @endif
                    <textarea id="input" name="input" rows="25" cols="50" >{{ $graph->toInputFormat() }}</textarea>
                </td>
                <td valign="top">
                    <button>Submit</button>
                </td>
            </tr>
        </table>


    </form>

@endsection
