@extends ('Layout.split-nopanel')
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

        .button{
            border:1px solid #333;
            background:#6479fd;
        }
        .button:hover{
            background:#a4a9fd;
        }
        .dialog{
            border:5px solid #666;
            padding:10px;
            background:#2d353c;
            position:absolute;
            display:none;
            top: 50px;
            left: 260px;
            width: 200px;
        }
        .dialog label{
            display:inline-block;
            color:#cecece;
        }
        input[type=text]{
            border:1px solid #333;
            display:inline-block;
            margin:5px;
        }
        #btnOK{
            border:1px solid #000;
            background:#8ec73a;
            margin:5px;
        }

        #btnOK:hover{
            border:1px solid #000;
            background:#8ec73a;
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
@section('content_top')
    <li><a href="">{{ trans("view.Header_Topography - Modems") }} </a></li>
@stop
@section('content_left')

    <div class="row">
        <div class="col align-self-start">
            <button id="to-json">To Json</button>
            <button id="add-amplifier">Add Amplifier</button>
            <button id="add-splitter">Add Splitter</button>
            <button id="add-house">Add House</button>
            <button id="add-line">Add Line</button>
            <div class="diagram" id="paper">
            </div>
            <div class="dialog" id="house_form">
                <form>
                    <label id="">
                        Number of modems:
                    </label>
                    <input type="text" id="no_modems">
                    <label id="">
                        House No:
                    </label>
                    <input type="text" id="house_no">

                    <div align="center">
                        <input type="button" value="Ok" id="btnOK">
                    </div>
                </form>
            </div>
{{--            <div class="row">--}}
{{--                @include ('bootstrap.panel', [--}}
{{--                    'content' => "graph_panel",--}}
{{--                    'view_header' => 'Vicinity Graph',--}}
{{--                    'height' => 'auto',--}}
{{--                    'i' => '1'--}}
{{--                ])--}}
{{--            </div>--}}
        </div>
    </div>

@stop

@section('javascript')
    <script src="{{asset('components/assets-admin/plugins/joint-js/js/lodash.min.js')}}"></script>
    <script src="{{asset('components/assets-admin/plugins/joint-js/js/backbone.min.js')}}"></script>
    <script src="{{asset('components/assets-admin/plugins/joint-js/js/joint.min.js')}}"></script>
    <script src="{{asset('components/assets-admin/plugins/joint-js/js/joint.custom.shapes.js')}}"></script>
    <script type="text/javascript">
        $(function() {
        $("#to-json").click(function () {
            console.log(JSON.stringify(graph.toJSON()));
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
            console.log('test')
            $("#house_form input[type=text]").val('');
            $("#house_form").show(500);
        });
        $("#btnOK").click(function() {
            var noModems = $("#no_modems").val().trim()
            var houseNo = $("#house_no").val().trim()
            if(noModems ==='' || houseNo ===''){
                $("#house_form").hide(400);
                return;
            }
            graph.addCell(custom.House.create(750, 200, noModems, houseNo));
            $("#house_form").hide(400);

        });
        $("#add-line").click(function () {
            graph.addCell(custom.LabeledSmoothLine.create([750, 250], [950, 250]));
        });
        });

        var graph_json = '{"cells":[{"type":"custom.Bus","z":-1,"source":{"x":800,"y":33},"target":{"x":50,"y":33},"labels":[{"attrs":{"labelText":{"text":"N+0","fontFamily":"monospace"}}}],"id":"aca455d2-3176-488b-b986-dbb16a2dd89f","parent":"6ab6771c-0431-4d6f-9ddb-ca82635f609e","attrs":{"line":{"stroke":"rgba(119,255,0,0.37)"}}},{"type":"custom.Bus","z":-1,"source":{"x":800,"y":125},"target":{"x":50,"y":125},"labels":[{"attrs":{"labelText":{"text":"N+1","fontFamily":"monospace"}}}],"id":"1d7b9ac8-3883-44b9-89b1-a815e6465906","attrs":{"line":{"stroke":"#330005"}}},{"type":"custom.Bus","z":-1,"source":{"x":800,"y":325},"target":{"x":50,"y":325},"labels":[{"attrs":{"labelText":{"text":"N+2","fontFamily":"monospace"}}}],"id":"6d62267b-0688-4b29-b66f-241cc1b030c9","attrs":{"line":{"stroke":"#000633"}}},{"type":"custom.Bus","z":-1,"source":{"x":800,"y":525},"target":{"x":50,"y":525},"labels":[{"attrs":{"labelText":{"text":"N+3","fontFamily":"monospace"}}}],"id":"64853f83-cd57-43ce-acdc-8a636fe84325","attrs":{"line":{"stroke":"#333301"}}},{"type":"custom.Bus","z":-1,"source":{"x":800,"y":625},"target":{"x":50,"y":625},"labels":[{"attrs":{"labelText":{"text":"N+4","fontFamily":"monospace"}}}],"id":"5c8f6c04-00f6-438d-b70f-cf04c52fffd1","attrs":{"line":{"stroke":"#ff5964"}}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"6ab6771c-0431-4d6f-9ddb-ca82635f609e"},"target":{"selector":"body","id":"9e8f0c4b-c46a-45a1-8e30-103cfb43337d"},"id":"60556b63-625a-4e33-abe7-89f939703bd5","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"9e8f0c4b-c46a-45a1-8e30-103cfb43337d"},"target":{"selector":"body","id":"8185ab19-4951-4229-b0ba-1c365b3bda33"},"id":"8936626d-7154-4a29-8440-47965d2b6dc1","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"8185ab19-4951-4229-b0ba-1c365b3bda33"},"target":{"selector":"body","id":"5d8b3679-3147-4a02-8090-1d713ed3c0d9"},"id":"b921d98a-5823-404c-9eba-b5d187c72872","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"98149c8e-4a4e-4dab-88e8-bcc9063c5c49"},"target":{"selector":"body","id":"59915c0b-cd7e-426e-822f-cdf8d01a93a8"},"id":"ed7009a3-5312-44c7-a2d0-3daa31b5dfd3","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"59915c0b-cd7e-426e-822f-cdf8d01a93a8"},"target":{"selector":"body","id":"3b5f955b-02b7-48d3-8f69-b8928f7be27d"},"id":"bdec4184-ca91-4503-9ef9-76fcd3cb6385","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"88a2729f-8585-4f80-870c-a279384dba4e"},"target":{"selector":"body","id":"9f94a3a3-318e-45dc-8a06-f58b0b8349fd"},"id":"ff6fc470-6947-46f9-af7e-13ede41b11cb","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"6ab6771c-0431-4d6f-9ddb-ca82635f609e"},"target":{"selector":"body","id":"98149c8e-4a4e-4dab-88e8-bcc9063c5c49"},"id":"fa977d56-8b0e-4b9b-a308-06ebe017da75","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"6ab6771c-0431-4d6f-9ddb-ca82635f609e"},"target":{"selector":"body","id":"88a2729f-8585-4f80-870c-a279384dba4e"},"id":"20afc741-6126-4419-991e-63f660dddb07","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"9f94a3a3-318e-45dc-8a06-f58b0b8349fd"},"target":{"selector":"body","id":"44ee6b87-43e6-41b4-b402-a39d94f43e33"},"id":"89e7171b-d430-40ca-9e73-e97817ccf61c","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"9f94a3a3-318e-45dc-8a06-f58b0b8349fd"},"target":{"selector":"body","id":"7432a33a-4221-4529-8af2-3284eabd53be"},"id":"ff1ba532-93fc-4a4e-ac19-e51b1882b50f","attrs":{}},{"type":"standard.Circle","position":{"x":40,"y":180},"size":{"width":25,"height":25},"angle":0,"id":"59915c0b-cd7e-426e-822f-cdf8d01a93a8","attrs":{}},{"type":"standard.Circle","position":{"x":260,"y":180},"size":{"width":25,"height":25},"angle":0,"id":"9f94a3a3-318e-45dc-8a06-f58b0b8349fd","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"59915c0b-cd7e-426e-822f-cdf8d01a93a8"},"target":{"selector":"body","id":"64b8f256-7b28-46f4-98b5-c067259ee3d8"},"id":"955d5ce7-78bb-473c-bf9c-0a8f5f71c2f4","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"9f94a3a3-318e-45dc-8a06-f58b0b8349fd"},"target":{"selector":"body","id":"40a0fa40-bfa8-4a30-bdef-b535e241dc06"},"id":"3b108e8a-91ec-4100-85d3-5e03bdb96987","attrs":{}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"bottom","args":{"rotate":true}},"position":{"x":450,"y":18},"angle":0,"id":"6ab6771c-0431-4d6f-9ddb-ca82635f609e","embeds":["aca455d2-3176-488b-b986-dbb16a2dd89f"],"attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"bottom","args":{"rotate":true}},"position":{"x":100,"y":110},"angle":0,"id":"98149c8e-4a4e-4dab-88e8-bcc9063c5c49","attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"bottom","args":{"rotate":true}},"position":{"x":260,"y":110},"angle":0,"id":"88a2729f-8585-4f80-870c-a279384dba4e","attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"bottom","args":{"rotate":true}},"position":{"x":450,"y":110},"angle":0,"id":"9e8f0c4b-c46a-45a1-8e30-103cfb43337d","attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"bottom","args":{"rotate":true}},"position":{"x":450,"y":310},"angle":0,"id":"8185ab19-4951-4229-b0ba-1c365b3bda33","attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"bottom","args":{"rotate":true}},"position":{"x":450,"y":510},"angle":0,"id":"5d8b3679-3147-4a02-8090-1d713ed3c0d9","attrs":{"label":{"text":"amplifier"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":40,"y":250},"angle":0,"id":"3b5f955b-02b7-48d3-8f69-b8928f7be27d","attrs":{"label":{"text":"1"},"labelHouseNo":{"text":"3"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":90,"y":250},"angle":0,"id":"64b8f256-7b28-46f4-98b5-c067259ee3d8","attrs":{"label":{"text":"2"},"labelHouseNo":{"text":"3"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":200,"y":250},"angle":0,"id":"40a0fa40-bfa8-4a30-bdef-b535e241dc06","attrs":{"label":{"text":"3"},"labelHouseNo":{"text":"3"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":250,"y":250},"angle":0,"id":"44ee6b87-43e6-41b4-b402-a39d94f43e33","attrs":{"label":{"text":"4"},"labelHouseNo":{"text":"4"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":300,"y":250},"angle":0,"id":"7432a33a-4221-4529-8af2-3284eabd53be","attrs":{"label":{"text":"5"},"labelHouseNo":{"text":"5"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":516,"y":247},"angle":0,"id":"6130f0ed-6f25-4c91-bfc0-2432b2a9a532","attrs":{"label":{"text":"6"},"labelHouseNo":{"text":"6"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":571,"y":247},"angle":0,"id":"230c2745-4660-4b8b-9309-bbe306ddf72d","attrs":{"label":{"text":"7"},"labelHouseNo":{"text":"7"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":542,"y":439},"angle":0,"id":"9b21e44a-46a6-4a4f-979a-6998a267d4c1","attrs":{"label":{"text":"8"},"labelHouseNo":{"text":"8"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":600,"y":440},"angle":0,"id":"f43dc24e-fd92-40ea-8ded-9973cae0c99a","attrs":{"label":{"text":"9"},"labelHouseNo":{"text":"9"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":660,"y":439},"angle":0,"id":"d6a933c1-ef53-46e2-898d-c691b3afe0df","attrs":{"label":{"text":"10"},"labelHouseNo":{"text":"10"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":244,"y":721},"angle":0,"id":"167ecdcc-70a2-4e82-9bae-fffa5087261a","attrs":{"label":{"text":"11"},"labelHouseNo":{"text":"11"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"bottom","args":{"rotate":true}},"position":{"x":249,"y":610},"angle":0,"id":"c085aac7-cca4-4ee2-9cfb-494fdc87d954","attrs":{"label":{"text":"amp"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"bottom","args":{"rotate":true}},"position":{"x":456,"y":611},"angle":0,"id":"87917408-f56e-48ca-8f86-4ef659b6cc2b","attrs":{"label":{"text":"amp"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":407,"y":719},"angle":0,"id":"5607ddd0-9821-4f0c-b3af-fc71204f00e4","attrs":{"label":{"text":"12"},"labelHouseNo":{"text":"12"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":474,"y":720},"angle":0,"id":"efb8088f-210d-49b1-baed-fa6e321500b2","attrs":{"label":{"text":"13"},"labelHouseNo":{"text":"13"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"bottom","args":{"rotate":true}},"position":{"x":618,"y":612},"angle":0,"id":"c5b0c199-7081-4a12-8115-40fffe8ce450","attrs":{"label":{"text":"amp"}}},{"type":"standard.Circle","position":{"x":549,"y":191},"size":{"width":25,"height":25},"angle":0,"id":"c3291d3a-19a4-497d-8bae-22da4f5689f9","z":3,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"c3291d3a-19a4-497d-8bae-22da4f5689f9"},"target":{"id":"6130f0ed-6f25-4c91-bfc0-2432b2a9a532"},"id":"c0366437-4cc9-4b84-9520-ea1a21d41464","z":4,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"9e8f0c4b-c46a-45a1-8e30-103cfb43337d"},"target":{"id":"c3291d3a-19a4-497d-8bae-22da4f5689f9"},"id":"e7defd2b-05ad-417a-ac5c-3e2bfba3877f","z":5,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"c3291d3a-19a4-497d-8bae-22da4f5689f9"},"target":{"id":"230c2745-4660-4b8b-9309-bbe306ddf72d"},"id":"f0827ec4-1e53-495c-89d6-3766818e7a3d","z":6,"attrs":{}},{"type":"standard.Circle","position":{"x":614,"y":379},"size":{"width":25,"height":25},"angle":0,"id":"39405477-47ea-466d-8d07-3a1b86c0a6e7","z":7,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"8185ab19-4951-4229-b0ba-1c365b3bda33"},"target":{"id":"39405477-47ea-466d-8d07-3a1b86c0a6e7"},"id":"ea32a998-4b8e-4e88-bba9-d1652cc8df00","z":8,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"39405477-47ea-466d-8d07-3a1b86c0a6e7"},"target":{"id":"9b21e44a-46a6-4a4f-979a-6998a267d4c1"},"id":"ec7ae75f-28a6-44a7-ae48-fdf172d6be55","z":9,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"39405477-47ea-466d-8d07-3a1b86c0a6e7"},"target":{"id":"f43dc24e-fd92-40ea-8ded-9973cae0c99a"},"id":"b6e73084-12ee-4c09-bab5-0f38bf5eefbf","z":10,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"39405477-47ea-466d-8d07-3a1b86c0a6e7"},"target":{"id":"d6a933c1-ef53-46e2-898d-c691b3afe0df"},"id":"a8c3bec9-be80-4a79-9ad1-0a3c0ec8a039","z":11,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"x":441,"y":525},"target":{"id":"c085aac7-cca4-4ee2-9cfb-494fdc87d954"},"id":"e1fdfc4e-8c8c-4763-abe7-e88260d85ceb","z":12,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"c085aac7-cca4-4ee2-9cfb-494fdc87d954"},"target":{"id":"167ecdcc-70a2-4e82-9bae-fffa5087261a"},"id":"3c36220d-67b1-4571-af36-cbf82dff62f7","z":13,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"x":463,"y":524},"target":{"id":"87917408-f56e-48ca-8f86-4ef659b6cc2b"},"id":"46917eb5-3a93-4f84-815f-3ee4cfec54eb","z":14,"attrs":{}},{"type":"standard.Circle","position":{"x":454,"y":668},"size":{"width":25,"height":25},"angle":0,"id":"f73ce33e-a979-47cb-a530-164a1bf79a02","z":15,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"87917408-f56e-48ca-8f86-4ef659b6cc2b"},"target":{"id":"f73ce33e-a979-47cb-a530-164a1bf79a02"},"id":"d9a6182a-4540-4cda-b0b4-b146c632ca7a","z":16,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"f73ce33e-a979-47cb-a530-164a1bf79a02"},"target":{"id":"5607ddd0-9821-4f0c-b3af-fc71204f00e4"},"id":"446337f5-0ea7-446d-b5c2-659588fe576f","z":17,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"f73ce33e-a979-47cb-a530-164a1bf79a02"},"target":{"id":"efb8088f-210d-49b1-baed-fa6e321500b2"},"id":"a030493e-4736-4c3a-a36b-3cd0ca242b25","z":18,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"5d8b3679-3147-4a02-8090-1d713ed3c0d9"},"target":{"id":"c5b0c199-7081-4a12-8115-40fffe8ce450"},"id":"fcfb16d2-36de-4e38-8175-4a100e128b0c","z":19,"attrs":{}}]}';

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
        var bus2 = custom.Bus.create(125, 'N+1', '#330005');
        var bus3 = custom.Bus.create(325, 'N+2', '#000633');
        var bus4 = custom.Bus.create(525, 'N+3', '#333301');
        var bus5 = custom.Bus.create(625, 'N+4', '#ff5964');

        var amplifier = custom.Amplifier.create(450, 10, 'amplifier');

        var amplifier21 = custom.Amplifier.create(100, 110, 'amplifier');
        var amplifier22 = custom.Amplifier.create(260, 110, 'amplifier');
        var amplifier23 = custom.Amplifier.create(450, 110, 'amplifier');

        var amplifier3 = custom.Amplifier.create(450, 310, 'amplifier');
        var amplifier4 = custom.Amplifier.create(450, 510, 'amplifier');

        var splitter1 = new joint.shapes.standard.Circle();
        splitter1.position(40, 180);
        splitter1.resize(25, 25);
        var splitter2 = new joint.shapes.standard.Circle();
        splitter2.position(260, 180);
        splitter2.resize(25, 25);

        var house  = custom.House.create(40, 250, '1', '3');
        var house2 = custom.House.create(90, 250, '2', '3');

        var house3 = custom.House.create(200,250, '3', '3');
        var house4 = custom.House.create(250,250, '4', '4');
        var house5 = custom.House.create(300,250, '5', '5');


        var connector14 = custom.LabeledSmoothLine.create(amplifier, amplifier23);
        var connector15 = custom.LabeledSmoothLine.create(amplifier23, amplifier3);
        var connector16 = custom.LabeledSmoothLine.create(amplifier3, amplifier4);
        var connector11 = custom.LabeledSmoothLine.create(amplifier21, splitter1);

        var a0a21 = custom.LabeledSmoothLine.create(amplifier, amplifier21);
        var a0a22 = custom.LabeledSmoothLine.create(amplifier, amplifier22);

        var a22s2 = custom.LabeledSmoothLine.create(amplifier22, splitter2);
        var s2h4 = custom.LabeledSmoothLine.create(splitter2, house4);
        var s2h5 = custom.LabeledSmoothLine.create(splitter2, house5);


        var connector12 = custom.LabeledSmoothLine.create(splitter1, house);
        smooth_line2 = custom.LabeledSmoothLine.create(splitter1, house2);
        smooth_line3 = custom.LabeledSmoothLine.create(splitter2, house3);


        amplifier.embed(bus1);

        $(document).ready(function () {

            // graph.resetCells([
            //     bus1,
            //     bus2,
            //     bus3,
            //     bus4,
            //     bus5,
            //
            //     connector14,
            //     connector15,
            //     connector16,
            //     connector11,
            //     connector12,
            //     a22s2,
            //     a0a21,
            //     a0a22,
            //
            //     s2h4,
            //     s2h5,
            //
            //     amplifier,
            //     amplifier21,
            //     amplifier22,
            //     amplifier23,
            //     amplifier3,
            //     amplifier4,
            //
            //     splitter1,
            //     splitter2,
            //
            //     house,
            //     house2,
            //     house3,
            //     house4,
            //     house5,
            //     smooth_line2,
            //     smooth_line3,
            //
            // ]);

            graph.fromJSON(JSON.parse(graph_json));
            paper.unfreeze();
        });
    </script>

@stop
