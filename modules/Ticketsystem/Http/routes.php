<?php

BaseRoute::group([], function() {

	BaseRoute::resource('Ticket', 'Modules\Ticketsystem\Http\Controllers\TicketController');
	BaseRoute::resource('Comment', 'Modules\Ticketsystem\Http\Controllers\CommentController');
	BaseRoute::resource('Ticketsystem', 'Modules\Ticketsystem\Http\Controllers\TicketsystemController');
	Route::post('Ticket/detach/{id}/{func}', ['as' => 'Ticket.detach', 'uses' => 'Modules\Ticketsystem\Http\Controllers\TicketController@detach']);

});
