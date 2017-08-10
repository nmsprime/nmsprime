<h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Contracts', 'Dashboard') }} {{ date('m/Y') }}</h4>
<p>
    @if ($contracts == 0)
        {{ \App\Http\Controllers\BaseViewController::translate_view('NoContracts', 'Dashboard') }}
    @else
        {{ $contracts }}
    @endif
</p>
