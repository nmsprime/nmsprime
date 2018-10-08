<?php

BaseRoute::group([], function () {
    BaseRoute::resource('Ticket', 'Modules\Ticketsystem\Http\Controllers\TicketController');
    BaseRoute::resource('TicketType', 'Modules\Ticketsystem\Http\Controllers\TicketTypeController');
    BaseRoute::resource('Comment', 'Modules\Ticketsystem\Http\Controllers\CommentController');

    BaseRoute::post('Ticket/detach/{id}/{func}', [
        'as' => 'Ticket.detach',
        'uses' => 'Modules\Ticketsystem\Http\Controllers\TicketController@detach',
        'middleware' => ['can:delete,Modules\Ticketsystem\Entities\Ticket'],
    ]);
});
