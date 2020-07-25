@extends ('Layout.default')
@section('head')
    <link rel="stylesheet" href="{{ asset('components/assets-admin/plugins/joint-js/css/joint.min.css') }}">
    <style>
        .active.joint-element [joint-selector="body"],
        .active.joint-type-custom-labeledsmoothline [joint-selector="line"] {
            stroke: #17d800;
            stroke-width: 3;
        }

        .active.joint-type-custom-bus [joint-selector="line"] {
            stroke: #17d800;
            stroke-width: 5;
            stroke-dasharray: 5, 5;
        }

    </style>
@stop
@section ('graph_panel')
    <button id="to-json">To Json</button>
    <button id="add-amplifier">Add Amplifier</button>
    <button id="add-splitter">Add Splitter</button>
    <button id="add-house">Add House</button>
    <button id="add-line">Add Line</button>
    <div class="diagram" id="paper">
    </div>
@stop

@section('content')

    <div class="col-md-12">

        <h1 class="page-header">{{ $title }}</h1>
        <div class="row">
            @include ('bootstrap.panel', [
                'content' => "graph_panel",
                'view_header' => 'Vicinity Graph',
                'height' => 'auto',
                'i' => '1'
            ])
        </div>
    </div>

@stop

@section('javascript')
    <script src="{{asset('components/assets-admin/plugins/joint-js/js/lodash.min.js')}}"></script>
    <script src="{{asset('components/assets-admin/plugins/joint-js/js/backbone.min.js')}}"></script>
    <script src="{{asset('components/assets-admin/plugins/joint-js/js/joint.js')}}"></script>
    <script src="{{asset('components/assets-admin/plugins/joint-js/js/joint.custom.shapes.js')}}"></script>
    <script type="text/javascript">
        $("#to-json").click(function () {
            console.log(graph.toJSON());
        });
        $("#add-amplifier").click(function () {
            graph.addCell(custom.Amplifier.create(750, 100, 'amp'));
        });
        $("#add-splitter").click(function () {
            var splitter = new joint.shapes.standard.Circle();
            splitter.position(750, 150);
            splitter.resize(25, 25);
            graph.addCell(splitter);
        });
        $("#add-house").click(function () {
            var house = new joint.shapes.standard.Polygon();
            house.resize(40, 40);
            house.position(750, 200);
            house.attr('label/text', '4');
            house.attr('body/refPoints', '1,0 2,1 2,3 1.25,3 1.25,2 0.75,2 0.75,3 0,3 0,1 0,1');
            graph.addCell(house);
        });
        $("#add-line").click(function () {
            graph.addCell(custom.LabeledSmoothLine.create([750, 250], [950, 250]));
        });


        var graph = new joint.dia.Graph;

        var paper = new joint.dia.Paper({
            el: document.getElementById('paper'),
            width: 1000,
            height: 800,
            model: graph,
            async: true,
            frozen: true,
            sorting: joint.dia.Paper.sorting.APPROX,
            restrictTranslate: true,
            defaultConnectionPoint: {
                name: 'boundary',
                args: {selector: 'body'}
            },
            defaultAnchor: {
                name: 'perpendicular'
            },
            defaultLinkAnchor: {
                name: 'connectionPerpendicular'
            },
            interactive: {
                linkMove: false,
                labelMove: false
            },
            highlighting: {
                default: {
                    name: 'addClass',
                    options: {
                        className: 'active'
                    }
                }
            }
        });
        paper.on('cell:mouseenter', function (cellView) {
            getCellOutbounds(this.model, cellView.model).forEach(function (cell) {
                cell.findView(this).highlight();
            }, this);
            if (cellView.model instanceof custom.LabeledSmoothLine) {
                tools = [
                    new joint.linkTools.SourceArrowhead(),
                    new joint.linkTools.TargetArrowhead(),
                    new joint.linkTools.Remove({distance: 20})
                ]
                cellView.addTools(new joint.dia.ToolsView({
                    name: 'onhover',
                    tools: tools
                }));
            }
        });

        paper.on('cell:mouseleave cell:pointerdown', function (cellView) {
            getCellOutbounds(this.model, cellView.model).forEach(function (cell) {
                cell.findView(this).unhighlight();
            }, this);
            if (cellView.model instanceof custom.LabeledSmoothLine) {
                tools = [
                    new joint.linkTools.SourceArrowhead(),
                    new joint.linkTools.TargetArrowhead(),
                    new joint.linkTools.Remove({distance: 20})
                ]
                cellView.removeTools();
            }
        });
        paper.on('link:pointermove', function (linkView, _evt, _x, y) {
            var link = linkView.model;
            if (link instanceof custom.Bus) {
                var sView = link.getSourcePoint();
                console.log(link)
                var tView = linkView.targetView;
                var padding = 20;
                var minY = 20;
                var maxY = 100;
                link.setStart(Math.min(Math.max(y - sView.y, minY), maxY));
            }
        });

        function getCellOutbounds(graph, cell) {
            return [cell].concat(
                graph.getNeighbors(cell, {outbound: true, inbound: true}),
                graph.getConnectedLinks(cell, {outbound: true, inbound: true, deep: true})
            );
        }

        // Create shapes
        var custom = joint.shapes.custom;
        var bus1 = custom.Bus.create(25, 'N+0', 'rgba(119,255,0,0.37)');
        var bus2 = custom.Bus.create(225, 'N+1', '#330005');
        var bus3 = custom.Bus.create(325, 'N+2', '#000633');
        var bus4 = custom.Bus.create(425, 'N+3', '#333301');
        var bus5 = custom.Bus.create(525, 'N+4', '#ff5964');

        var amplifier = custom.Amplifier.create(365, 10, 'amplifier');
        var amplifier2 = custom.Amplifier.create(365, 210, 'amplifier');
        var amplifier3 = custom.Amplifier.create(365, 310, 'amplifier');
        var amplifier4 = custom.Amplifier.create(365, 410, 'amplifier');

        var splitter = new joint.shapes.standard.Circle();
        splitter.position(100, 40);
        splitter.resize(25, 25);

        var house = new joint.shapes.standard.Polygon();
        house.resize(40, 40);
        house.position(40, 140);
        house.attr('label/text', '4');
        house.attr('body/refPoints', '1,0 2,1 2,3 1.25,3 1.25,2 0.75,2 0.75,3 0,3 0,1 0,1');

        var house2 = new joint.shapes.standard.Polygon();
        house2.resize(40, 40);
        house2.position(90, 140);
        house2.attr('label/text', '3');
        house2.attr('body/refPoints', '1,0 2,1 2,3 1.25,3 1.25,2 0.75,2 0.75,3 0,3 0,1 0,1');

        var house3 = new joint.shapes.standard.Polygon();
        house3.resize(40, 40);
        house3.position(140, 140);
        house3.attr('label/text', '1');
        house3.attr('body/refPoints', '1,0 2,1 2,3 1.25,3 1.25,2 0.75,2 0.75,3 0,3 0,1 0,1');


        var connector14 = custom.LabeledSmoothLine.create(amplifier, amplifier2);
        var connector15 = custom.LabeledSmoothLine.create(amplifier2, amplifier3);
        var connector16 = custom.LabeledSmoothLine.create(amplifier3, amplifier4);
        var connector11 = custom.LabeledSmoothLine.create(amplifier, splitter);
        var connector12 = custom.LabeledSmoothLine.create(splitter, house);

        smooth_line2 = custom.LabeledSmoothLine.create(splitter, house2);
        smooth_line3 = custom.LabeledSmoothLine.create(splitter, house3);


        amplifier.embed(bus1);

        $(document).ready(function () {

            graph.resetCells([
                bus1,
                bus2,
                bus3,
                bus4,
                bus5,

                connector14,
                connector15,
                connector16,
                connector11,
                connector12,

                amplifier,
                amplifier2,
                amplifier3,
                amplifier4,
                splitter,
                house,
                house2,
                house3,
                smooth_line2,
                smooth_line3,

            ]);

            paper.unfreeze();
        });
    </script>

@stop
