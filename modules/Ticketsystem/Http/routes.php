<?php

BaseRoute::group([], function() {
	
	BaseRoute::resource('Ticket', 'Modules\Ticketsystem\Http\Controllers\TicketController');
	BaseRoute::resource('Comment', 'Modules\Ticketsystem\Http\Controllers\CommentController');
	BaseRoute::resource('Assignee', 'Modules\Ticketsystem\Http\Controllers\AssigneeController');
	BaseRoute::resource('Ticketsystem', 'Modules\Ticketsystem\Http\Controllers\TicketsystemController');
});
