@extends ('Layout.split-nopanel')

@section('content_top')
    @if (isset($breadcrumb) && $breadcrumb)
        <li class="active">
            <a href="{{ $breadcrumb }}">
            <i class="sitemap"></i>Entity Diagram</a>
        </li>
    @endif
    <li>
        <a href="">{{ 'Diagrams - Modems' }} </a>
    </li>

@stop

{{ $view_header = 'Diagrams' }}
@section('content_left')

  @if ($monitoring)
    @foreach ($monitoring as $mon)

      @if ($loop->first)
        <form action="" method="GET">
          <div class="row">
            <div class="col-xs-3">From: {!!Form::input('text', 'from', $mon['from'], ['style' => 'simple'])!!}</div>
            <div class="col-xs-3">To: {!!Form::input('text' ,'to', $mon['to'], ['style' => 'simple'])!!}</div>
            @if (Route::current()->getName() != 'ProvMon.diagram_edit')
                    <div class="col-xs-4">{!!Form::select('row', array_merge(config('hfcreq.hfParameters'), ['microreflections' => 'Microreflections', 'errors' => 'Errors', 'traffic' => 'Traffic', 'all' => 'ALL']), $mon['row'], [], ['style' => 'simple', 'class' => 'pull-right'])!!}</div>
            @endif
            <div class="col-xs-3">{!!Form::submit('Submit', ['style' => 'simple'])!!}</div>
          </div>
        </form>
      <div>
      @endif

      @if (isset($mon['descr']))
        <div><h4>{{$mon['descr']}}</h4></div>
      @endif
      <div class="d-flex flex-wrap justify-content-center m-b-5">
      @foreach ($mon['graphs'] as $id => $graph)
        <img class="m-b-5" height="230" src={{$graph}}></img>
      @endforeach
      </div>

    @endforeach
    </div>

  @else
    <font color="red">No Diagrams available</font><br>
    This could be because the Modem was not online until now. Please note that Diagrams are only available
    from the point that a modem was online. If all diagrams did not show properly then it should be a
    bigger problem and there should be a cacti misconfiguration. Please consider the administrator on bigger problems.
  @endif

@stop

@section ('historyTable')
    @include ('HfcBase::history.table')
@endsection

@section ('historySlider')
    @include ('HfcBase::history.slider')
@endsection

@section('javascript')
  @if (isset($withHistory))
      @include ('HfcBase::history.javascript')
  @endif
@stop
