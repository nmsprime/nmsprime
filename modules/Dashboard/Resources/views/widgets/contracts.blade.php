<h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Contracts', 'Dashboard') }}</h4>
<p>
    @if ($data['contracts']['total'])
        {{ $data['contracts']['total'] }}
    @else
        {{ \App\Http\Controllers\BaseViewController::translate_view('NoContracts', 'Dashboard') }}
    @endif
</p>
