<h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Tickets', 'Dashboard') }}</h4>
<p>
    @if ($new_tickets == 0)
        {{ \App\Http\Controllers\BaseViewController::translate_view('NoTickets', 'Dashboard') }}
    @else
        {{ $new_tickets }}
    @endif
</p>
