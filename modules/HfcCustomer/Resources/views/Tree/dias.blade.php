@extends ('Layout.single')

<head>
</head>

@section('content_top')
        Diagrams - Modems
@stop

@section('content_left')

        <h1>Diagrams</h1>

        @if ($monitoring)

                <table>
                @foreach ($monitoring as $mon)

                        @if ($mon === reset($monitoring))
                                <tr><td>
                                <form action="" method="GET">
                                        From:<input type="text" name="from" value={{$mon['from']}}>
                                        To:<input type="text" name="to" value={{$mon['to']}}>
                                        <input type="submit" value="Submit">
                                </form>
                                </td></tr>
                        @endif

                        <tr><td><h4>{{$mon['descr']}}</h4></td></tr>
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

