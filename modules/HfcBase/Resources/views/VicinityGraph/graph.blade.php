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
            graph.addCell(custom.Amplifier.create(950, 100, 'amp'));
        });
        $("#add-splitter").click(function () {
            var splitter = new joint.shapes.standard.Circle();
            splitter.position(950, 150);
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
            graph.addCell(custom.House.create(950, 200, noModems, houseNo));
            $("#house_form").hide(400);

        });
        $("#add-line").click(function () {
            graph.addCell(custom.LabeledSmoothLine.create([950, 250], [1150, 250]));
        });
        });

        var graph_json = '{"cells":[{"type":"custom.Bus","z":-1,"source":{"x":900,"y":30},"target":{"x":50,"y":30},"labels":[{"attrs":{"labelText":{"text":"N+0","fontFamily":"monospace"}}}],"id":"86904b0e-41de-4165-b60b-78911dba664f","parent":"fff4d70a-f735-414e-88e0-68037e3dfd9f","attrs":{"line":{"stroke":"rgba(119,255,0,0.37)"}}},{"type":"custom.Bus","z":-1,"source":{"x":900,"y":150},"target":{"x":50,"y":150},"labels":[{"attrs":{"labelText":{"text":"N+1","fontFamily":"monospace"}}}],"id":"3fe81c49-a62b-497f-867c-7062fe2c6c2d","parent":"a37fee1c-907d-4330-a6a4-21971ecb980b","attrs":{"line":{"stroke":"#330005"}}},{"type":"custom.Bus","z":-1,"source":{"x":897,"y":347},"target":{"x":47,"y":347},"labels":[{"attrs":{"labelText":{"text":"N+2","fontFamily":"monospace"}}}],"id":"c1931b75-f7b2-47d2-b484-4fca1d515fcf","parent":"f739f40a-f1f2-4dc9-b7fc-b01dc86d8cc9","attrs":{"line":{"stroke":"#000633"}}},{"type":"custom.Bus","z":-1,"source":{"x":900,"y":530},"target":{"x":50,"y":530},"labels":[{"attrs":{"labelText":{"text":"N+3","fontFamily":"monospace"}}}],"id":"d69878d6-9c7e-4db0-9be8-babe7e4ec102","parent":"e66977c8-e894-4e8b-b5a6-e1cb6acbba36","attrs":{"line":{"stroke":"#333301"}}},{"type":"custom.Bus","z":-1,"source":{"x":900,"y":630},"target":{"x":50,"y":630},"labels":[{"attrs":{"labelText":{"text":"N+4","fontFamily":"monospace"}}}],"id":"3bdf5912-c25d-44e9-b632-2824d02c06a1","parent":"1347aec9-fcc8-426d-b0af-317c88c86e4e","attrs":{"line":{"stroke":"#ff5964"}}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"fff4d70a-f735-414e-88e0-68037e3dfd9f"},"target":{"selector":"body","id":"a37fee1c-907d-4330-a6a4-21971ecb980b"},"id":"11598cf5-d50e-4a59-8a49-045d5e8ac1e4","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"f739f40a-f1f2-4dc9-b7fc-b01dc86d8cc9"},"target":{"selector":"body","id":"e66977c8-e894-4e8b-b5a6-e1cb6acbba36"},"id":"95887264-c8bb-48e0-9c09-fbdb380662fc","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"e66977c8-e894-4e8b-b5a6-e1cb6acbba36"},"target":{"selector":"body","id":"1347aec9-fcc8-426d-b0af-317c88c86e4e"},"id":"3e3678f0-2a43-420f-98b7-60ba38b11c04","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"2b8c8372-e353-4246-bd03-218f519f1ffe"},"target":{"selector":"body","id":"03c74cd5-d7b1-49bd-9668-957da1f550e5"},"id":"159dbe55-e564-4f1e-ba69-ecc4c754e602","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"03c74cd5-d7b1-49bd-9668-957da1f550e5"},"target":{"selector":"body","id":"9c808a22-9c15-41b9-b436-7d7b48587bc0"},"id":"bda432f5-deca-474c-a778-dbfeb9d1ba1d","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"d8dc2281-ef31-4a46-a093-2f56045e3aa0"},"target":{"selector":"body","id":"9fc5dca6-e8a4-481b-aa5b-e84c221e65c4"},"id":"38715b10-3137-4ac7-83a5-4f1be23322b6","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"fff4d70a-f735-414e-88e0-68037e3dfd9f"},"target":{"selector":"body","id":"2b8c8372-e353-4246-bd03-218f519f1ffe"},"id":"4ec27fe5-e969-4742-87f1-16eebbe277d0","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"9fc5dca6-e8a4-481b-aa5b-e84c221e65c4"},"target":{"selector":"body","id":"0107a4c6-c382-4b49-a1fe-54f4bf90fa64"},"id":"e3d3fc8f-17bd-426c-8cb9-e6f9de063fa3","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"9fc5dca6-e8a4-481b-aa5b-e84c221e65c4"},"target":{"selector":"body","id":"41794f9b-f6f5-4340-8e8b-68c9ebe375eb"},"id":"753b7273-20c7-4e2d-8e25-94aa2489c1db","attrs":{}},{"type":"standard.Circle","position":{"x":97,"y":203},"size":{"width":25,"height":25},"angle":0,"id":"03c74cd5-d7b1-49bd-9668-957da1f550e5","attrs":{}},{"type":"standard.Circle","position":{"x":259,"y":206},"size":{"width":25,"height":25},"angle":0,"id":"9fc5dca6-e8a4-481b-aa5b-e84c221e65c4","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"03c74cd5-d7b1-49bd-9668-957da1f550e5"},"target":{"selector":"body","id":"0cadea6b-12aa-43db-9d2f-302c27ba1a6b"},"id":"2af86896-ba7d-478f-8a69-7c6015d5adb6","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"9fc5dca6-e8a4-481b-aa5b-e84c221e65c4"},"target":{"selector":"body","id":"241e8a33-de46-4afe-822d-e5e627d3596d"},"id":"3d5bbde0-fe2b-43d3-9dd2-afd61473ac61","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"fff4d70a-f735-414e-88e0-68037e3dfd9f"},"target":{"id":"d8dc2281-ef31-4a46-a093-2f56045e3aa0"},"id":"34a701c1-8df0-42ba-95d5-43dfcdd88a27","attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"selector":"body","id":"a37fee1c-907d-4330-a6a4-21971ecb980b"},"target":{"id":"f739f40a-f1f2-4dc9-b7fc-b01dc86d8cc9"},"id":"675c51b9-da19-4905-b290-b2d2f4fad583","attrs":{}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"center","args":{"rotate":true}},"position":{"x":600,"y":15},"angle":0,"id":"fff4d70a-f735-414e-88e0-68037e3dfd9f","embeds":["86904b0e-41de-4165-b60b-78911dba664f"],"attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"center","args":{"rotate":true}},"position":{"x":97,"y":133},"angle":0,"id":"2b8c8372-e353-4246-bd03-218f519f1ffe","attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"center","args":{"rotate":true}},"position":{"x":262,"y":135},"angle":0,"id":"d8dc2281-ef31-4a46-a093-2f56045e3aa0","attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"center","args":{"rotate":true}},"position":{"x":600,"y":135},"angle":0,"id":"a37fee1c-907d-4330-a6a4-21971ecb980b","embeds":["3fe81c49-a62b-497f-867c-7062fe2c6c2d"],"attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"center","args":{"rotate":true}},"position":{"x":597,"y":332},"angle":0,"id":"f739f40a-f1f2-4dc9-b7fc-b01dc86d8cc9","embeds":["c1931b75-f7b2-47d2-b484-4fca1d515fcf"],"attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"center","args":{"rotate":true}},"position":{"x":600,"y":515},"angle":0,"id":"e66977c8-e894-4e8b-b5a6-e1cb6acbba36","embeds":["d69878d6-9c7e-4db0-9be8-babe7e4ec102"],"attrs":{"label":{"text":"amplifier"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"center","args":{"rotate":true}},"position":{"x":600,"y":615},"angle":0,"id":"1347aec9-fcc8-426d-b0af-317c88c86e4e","embeds":["3bdf5912-c25d-44e9-b632-2824d02c06a1"],"attrs":{"label":{"text":"amplifier"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":56,"y":273},"angle":0,"id":"9c808a22-9c15-41b9-b436-7d7b48587bc0","attrs":{"label":{"text":"1"},"labelHouseNo":{"text":"3"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":113,"y":274},"angle":0,"id":"0cadea6b-12aa-43db-9d2f-302c27ba1a6b","attrs":{"label":{"text":"2"},"labelHouseNo":{"text":"3"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":194,"y":274},"angle":0,"id":"241e8a33-de46-4afe-822d-e5e627d3596d","attrs":{"label":{"text":"3"},"labelHouseNo":{"text":"3"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":249,"y":275},"angle":0,"id":"0107a4c6-c382-4b49-a1fe-54f4bf90fa64","attrs":{"label":{"text":"4"},"labelHouseNo":{"text":"4"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":308,"y":274},"angle":0,"id":"41794f9b-f6f5-4340-8e8b-68c9ebe375eb","attrs":{"label":{"text":"5"},"labelHouseNo":{"text":"5"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":395,"y":273},"angle":0,"id":"eb44149b-d671-43a5-baa8-55054e952be3","attrs":{"label":{"text":"6"},"labelHouseNo":{"text":"6"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":456,"y":273},"angle":0,"id":"e188e0e1-344a-4fc9-99bb-94c6d1984b7e","attrs":{"label":{"text":"7"},"labelHouseNo":{"text":"7"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":518,"y":273},"angle":0,"id":"e842bdb8-4195-47c0-9795-7c71c5806181","attrs":{"label":{"text":"8"},"labelHouseNo":{"text":"8"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":672,"y":269},"angle":0,"id":"4de69c95-3610-425c-906d-fcbd42634844","attrs":{"label":{"text":"2"},"labelHouseNo":{"text":"23"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":733,"y":268},"angle":0,"id":"8051a835-2d79-43b6-9976-d8011c79a4dc","attrs":{"label":{"text":"1"},"labelHouseNo":{"text":"24"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":673,"y":457},"angle":0,"id":"57560046-31b9-4192-9bf3-554dd64e6cee","attrs":{"label":{"text":"2"},"labelHouseNo":{"text":"25"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":731,"y":458},"angle":0,"id":"f1913a91-46d7-41dc-a169-e73ed2c7d7ee","attrs":{"label":{"text":"1"},"labelHouseNo":{"text":"26"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":788,"y":457},"angle":0,"id":"bca443d5-2faa-44a5-a0cf-35ac53b1d05b","attrs":{"label":{"text":"1"},"labelHouseNo":{"text":"27"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"center","args":{"rotate":true}},"position":{"x":369,"y":615},"angle":0,"id":"641a5cf2-06dc-4dd9-b7b2-3a9517e2b756","attrs":{"label":{"text":"amp"}}},{"type":"custom.Amplifier","z":2,"size":{"width":30,"height":30},"anchor":{"name":"center","args":{"rotate":true}},"position":{"x":809,"y":615},"angle":0,"id":"4333afdd-862f-4f14-8dfd-c02e7b1984cb","attrs":{"label":{"text":"amp"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":359,"y":689},"angle":0,"id":"d4cbe182-273d-44c4-9779-aac3a5a4009c","attrs":{"label":{"text":"2"},"labelHouseNo":{"text":"9"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":559,"y":728},"angle":0,"id":"405e4602-a26b-44fe-ba7f-5e24dc593699","attrs":{"label":{"text":"1"},"labelHouseNo":{"text":"10"}}},{"type":"custom.House","z":2,"size":{"width":40,"height":40},"anchor":{"name":"midSide","args":{"rotate":true}},"position":{"x":628,"y":727},"angle":0,"id":"430f12da-7431-4ea3-90c7-c6cb1886741e","attrs":{"label":{"text":"1"},"labelHouseNo":{"text":"11"}}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"a37fee1c-907d-4330-a6a4-21971ecb980b"},"target":{"id":"f06474c7-a798-450d-98bb-7118a082d10c"},"id":"ec26ae0a-8303-4a48-872a-1a1d0ae0ef71","z":3,"attrs":{}},{"type":"standard.Circle","position":{"x":463,"y":200},"size":{"width":25,"height":25},"angle":0,"id":"f06474c7-a798-450d-98bb-7118a082d10c","z":4,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"f06474c7-a798-450d-98bb-7118a082d10c"},"target":{"id":"eb44149b-d671-43a5-baa8-55054e952be3"},"id":"0e5a46e5-2696-4a64-a841-4c7c2990439b","z":5,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"f06474c7-a798-450d-98bb-7118a082d10c"},"target":{"id":"e188e0e1-344a-4fc9-99bb-94c6d1984b7e"},"id":"e4eb60b7-a509-4149-b571-d0e4fcaa201f","z":6,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"f06474c7-a798-450d-98bb-7118a082d10c"},"target":{"id":"e842bdb8-4195-47c0-9795-7c71c5806181"},"id":"e53f1e37-6f2b-4016-9868-8736f2e12e34","z":7,"attrs":{}},{"type":"standard.Circle","position":{"x":710,"y":204},"size":{"width":25,"height":25},"angle":0,"id":"ab9f66ee-5322-4e66-8e3f-c0e0a5fac80c","z":8,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"a37fee1c-907d-4330-a6a4-21971ecb980b"},"target":{"id":"ab9f66ee-5322-4e66-8e3f-c0e0a5fac80c"},"id":"bb138056-637c-42ac-bf7c-106bde427c53","z":9,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"ab9f66ee-5322-4e66-8e3f-c0e0a5fac80c"},"target":{"id":"4de69c95-3610-425c-906d-fcbd42634844"},"id":"663fcd88-2f9f-4f46-a219-b70485419231","z":10,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"ab9f66ee-5322-4e66-8e3f-c0e0a5fac80c"},"target":{"id":"8051a835-2d79-43b6-9976-d8011c79a4dc"},"id":"c31c7ddd-ec2a-4b58-a07b-45f8df127a41","z":11,"attrs":{}},{"type":"standard.Circle","position":{"x":737,"y":384},"size":{"width":25,"height":25},"angle":0,"id":"a484e915-ced1-4eea-ade8-23eec8c39c73","z":12,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"f739f40a-f1f2-4dc9-b7fc-b01dc86d8cc9"},"target":{"id":"a484e915-ced1-4eea-ade8-23eec8c39c73"},"id":"3fcbd37e-2819-4da3-8609-8638d0f91c75","z":13,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"a484e915-ced1-4eea-ade8-23eec8c39c73"},"target":{"id":"57560046-31b9-4192-9bf3-554dd64e6cee"},"id":"18a68afc-59a0-4bab-b2e8-3a43470b35bb","z":14,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"a484e915-ced1-4eea-ade8-23eec8c39c73"},"target":{"id":"f1913a91-46d7-41dc-a169-e73ed2c7d7ee"},"id":"7a0f1f8b-9913-45aa-b238-aed0cd6e35c3","z":15,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"a484e915-ced1-4eea-ade8-23eec8c39c73"},"target":{"id":"bca443d5-2faa-44a5-a0cf-35ac53b1d05b"},"id":"4ff2ade1-2795-4add-a60e-feca840debaf","z":16,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"641a5cf2-06dc-4dd9-b7b2-3a9517e2b756"},"target":{"id":"d4cbe182-273d-44c4-9779-aac3a5a4009c"},"id":"df4ac54b-c7f6-4268-9cf5-780fd3a64ae2","z":17,"attrs":{}},{"type":"standard.Circle","position":{"x":602,"y":679},"size":{"width":25,"height":25},"angle":0,"id":"70471821-0a99-45d9-ad47-a29869fd0c0f","z":18,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"1347aec9-fcc8-426d-b0af-317c88c86e4e"},"target":{"id":"70471821-0a99-45d9-ad47-a29869fd0c0f"},"id":"9f0bdb04-adeb-47c5-808e-1df87b291e67","z":19,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"70471821-0a99-45d9-ad47-a29869fd0c0f"},"target":{"id":"405e4602-a26b-44fe-ba7f-5e24dc593699"},"id":"32d685af-5aba-48bd-ad1e-acf3e8ccb686","z":20,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"70471821-0a99-45d9-ad47-a29869fd0c0f"},"target":{"id":"430f12da-7431-4ea3-90c7-c6cb1886741e"},"id":"a837ccd4-fa52-4d71-ab61-e1cff3a3bac2","z":21,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"e66977c8-e894-4e8b-b5a6-e1cb6acbba36"},"target":{"id":"641a5cf2-06dc-4dd9-b7b2-3a9517e2b756"},"id":"014261b6-830e-4d4c-bef5-c91efdf7feec","z":22,"attrs":{}},{"type":"custom.LabeledSmoothLine","connector":{"name":"smooth"},"attr":{"name":"smoothed-line"},"source":{"id":"e66977c8-e894-4e8b-b5a6-e1cb6acbba36"},"target":{"id":"4333afdd-862f-4f14-8dfd-c02e7b1984cb"},"id":"68133bf3-c96f-45c9-8c3e-ba0619a9593f","z":23,"attrs":{}}]}';

        var graph = new joint.dia.Graph;

        var paper = new joint.dia.Paper({
            el: document.getElementById('paper'),
            width: 1200,
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
        var bus1 = custom.Bus.create(30, 'N+0', 'rgba(119,255,0,0.37)');
        var bus2 = custom.Bus.create(130, 'N+1', '#330005');
        var bus3 = custom.Bus.create(330, 'N+2', '#000633');
        var bus4 = custom.Bus.create(530, 'N+3', '#333301');
        var bus5 = custom.Bus.create(630, 'N+4', '#ff5964');

        var amplifier = custom.Amplifier.create(600, 15, 'amplifier');

        var amplifier21 = custom.Amplifier.create(100, 115, 'amplifier');
        var amplifier22 = custom.Amplifier.create(260, 115, 'amplifier');
        var amplifier23 = custom.Amplifier.create(600, 115, 'amplifier');

        var amplifier3 = custom.Amplifier.create(600, 315, 'amplifier');
        var amplifier4 = custom.Amplifier.create(600, 515, 'amplifier');
        var amplifier5 = custom.Amplifier.create(600, 615, 'amplifier');

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
        var connector17 = custom.LabeledSmoothLine.create(amplifier4, amplifier5);
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
        amplifier23.embed(bus2);
        amplifier3.embed(bus3);
        amplifier4.embed(bus4);
        amplifier5.embed(bus5);

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
            //     connector17,
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
            //     amplifier5,
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
