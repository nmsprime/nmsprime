{{ trans('messages.hello') }} {{ $user->first_name }},

{{ $ticketAssigned }} <br />

{{ trans('messages.ticket') }} ID: {{ link_to_route('Ticket.edit', $ticket->id, ['id' => $ticket->id]) }} <br />
@if (isset($ticket->name))
{{ trans('messages.title') }}: {{ $ticket->name }} <br />
@endif
@if (isset($ticket->description))
{{ trans('messages.description') }}: {{ $ticket->description }} <br />
@endif

