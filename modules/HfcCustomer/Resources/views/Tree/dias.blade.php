@extends ('Layout.single')

<head>
</head>

@section('content_top')
        Diagrams - Modems
@stop

{{ $view_header = 'Diagrams' }}
@section('content_left')

        @if ($monitoring)

                <table>
                @foreach ($monitoring as $mon)

                        @if ($mon === reset($monitoring))
                                <tr><td>
                                <form action="" method="GET">
                                        <div class="row">
                                                <div class="col-xs-3">From: {!!Form::input('text', 'from', $mon['from'], ['style' => 'simple'])!!}</div>
                                                <div class="col-xs-3">To: {!!Form::input('text' ,'to', $mon['to'], ['style' => 'simple'])!!}</div>
                                                @if(Route::current()->getName() != 'ProvMon.diagram_edit')
                                                        <div class="col-xs-4">{!!Form::select('row', ['us_pwr' => 'US Power', 'us_snr' => 'US SNR', 'ds_pwr' => 'DS Power', 'ds_snr' => 'DS SNR', 'all' => 'ALL'], $mon['row'], ['style' => 'simple', 'class' => 'pull-right'])!!}</div>
                                                @endif
                                                <div class="col-xs-3">{!!Form::submit('Submit', ['style' => 'simple'])!!}</div>
                                        </div>
                                </form>


                                </td></tr>
                        @endif

                        @if (isset($mon['descr']))
                                <tr><td><h4>{{$mon['descr']}}</h4></td></tr>
                        @endif
                        <tr>
                        @foreach ($mon['graphs'] as $id => $graph)
                                <td><img height="230" src={{$graph}}></img></td>
                        @endforeach
                        </tr>

                @endforeach
                </table>

        @else
                <font color="red">No Diagrams available</font><br>
                This could be because the Modem was not online until now. Please note that Diagrams are only available
                from the point that a modem was online. If all diagrams did not show properly then it should be a
                bigger problem and there should be a cacti misconfiguration. Please consider the administrator on bigger problems.
        @endif


@stop

