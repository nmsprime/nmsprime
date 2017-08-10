<h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Net Income', 'Dashboard') }} {{ date('m/Y') }}</h4>
<p>
    @if (isset($income['total']))
        {{ number_format($income['total'], 0, ',', '.') }}
    @else
        {{ number_format(0, 0, ',', '.') }}
    @endif
</p>