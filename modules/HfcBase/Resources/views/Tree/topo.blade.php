@extends ('Layout.split-nopanel')

@section('head')
    <link href="{{asset('/modules/hfcbase/alert.css')}}" rel="stylesheet" type="text/css" media="screen"/>
    <script type="text/javascript" src="{{asset('/modules/hfcbase/alert.js')}}"></script>

    <script async defer src="{{asset('/modules/hfcbase/OpenLayers-2.13.1/OpenLayers.js')}}"></script>
    <script async defer src="https://maps.google.com/maps/api/js?v=3.2&sensor=false&key={{config('app.googleApiKey')}}"></script>

    <link rel="stylesheet" href="{{asset('components/assets-admin/plugins/leaflet/css/leaflet.css')}}"/>
    <script src="{{asset('components/assets-admin/plugins/leaflet/js/leaflet.js')}}"></script>
    <script src="{{asset('components/assets-admin/plugins/leaflet/js/leaflet-heat.js')}}"></script>

    @include ('HfcBase::Tree.topo-api')
@stop

@section ('historyTable')
    @include ('HfcBase::history.table')
@endsection

@section ('historySlider')
    @include ('HfcBase::history.slider')
@endsection

@section('content_top')
    @if (isset($breadcrumb) && $breadcrumb)
        <li class="active">
            <a href="{{ $breadcrumb }}">
            <i class="sitemap"></i>Entity Diagram</a>
        </li>
    @endif

    <li>
        <a href="">{{ trans("view.Header_Topography - Modems") }} </a>
    </li>
@stop

@section('content_left')
    <div class="row">
        <div class="col align-self-start">
            <div data-toggle="buttons">
                <label class="btn btn-primary active m-5">
                    <input type="radio" name="options" value="none" id="noneToggle" onchange="toggleControl(this);" autocomplete="off" checked/>
                    {{ trans("view.navigate") }}
                </label>
                <label class="btn btn-primary m-5">
                    <input type="radio" name="options" value="box" id="boxToggle" onchange="toggleControl(this);" autocomplete="off"/>
                    {{ trans("view.draw box") }}
                </label>
                <label class="btn btn-primary m-5">
                    <input type="radio" name="options" value="polygon" id="polygonToggle" onchange="toggleControl(this);" autocomplete="off"/>
                    {{ trans("view.draw polygon") }}
                </label>
                <label class="btn btn-primary m-5">
                    <input type="radio" name="options" value="modify" id="modifyToggle" onchange="toggleControl(this);" autocomplete="off" />
                    {{ trans("view.modify") }}
                </label>
                @if (isset($models))
                    <select onchange="redirect();" id="show-value">
                        @foreach($models as $model)
                            <option {{ array_key_exists('model', $_GET) && $model == $_GET['model'] ? 'selected' : ''}}>{{ $model }}</option>
                        @endforeach
                            <option {{! array_key_exists('model', $_GET) ? 'selected' : ''}}>{{ trans('messages.all') }}</option>
                    </select>
                @endif
            </div>
        </div>
        @if (! isset($field))
        <ul class="nav nav-pills align-self-end ml-auto mr-5">
            <?php
                $par = array_merge(Route::getCurrentRoute()->parameters(), \Request::all());
                $cur_row = Request::input('row', 'us_pwr');
                foreach (array_merge(config('hfcreq.hfParameters'), ['ds_us' => 'DS/US Power']) as $key => $val) {
                    $par['row'] = $key;
                    $class = ($cur_row === $key) ? 'active' : '';
                    echo("<li role=\"presentation\" class=\"$class\">".link_to_route(Route::getCurrentRoute()->getName(), $val, $par).'</li>');
                }
            ?>
        </ul>
        @endif
    </div>
    <div class="container-fluid m-t-20 m-b-20">
        @if (isset($dim) && isset($point))
            <div id="mapid" style="width: 100%; height: 75vh; max-height:575px; position: relative; outline: none;"></div>
        @else
            <div class="col-md-12 d-flex" id="map" style="height:75vh;max-height:575px"></div>
        @endif
    </div>

@stop

@section('javascript')
<script type="text/javascript">

function redirect()
{
    var element = document.getElementById('show-value');
    var text = element.options[element.selectedIndex].text;
    var url = window.location.href;
    var concat = '?';

    if (text == "{{ trans('messages.all') }}") {
        window.location.href = url.search(/model=/) == -1 ? url : url.replace(/\??&?model=\w+[^?&]+/, '');

        return;
    }

    if (url.search(/[?]/) != -1) {
        var concat = '&';
    }

    window.location.href = url.search(/model=/) == -1 ? url + concat + 'model=' + text : url.replace(/model=\w+[^?&]+/, 'model=' + text);
}

</script>

@if (isset($withHistory))
    @include ('HfcBase::history.javascript')
@endif
@stop
