<?php

namespace Modules\Ticketsystem\Entities;

return [
    'name' => 'Ticket',
    'link' => 'Ticket.dashboard',
    'MenuItems' => [
    'Tickets' => [
      'link' => 'Ticket.index',
            'icon'	=> 'fa-ticket',
            'class' => Ticket::class,
        ],
      'TicketTypes' => [
            'link' => 'TicketType.index',
            'icon'	=> 'fa-ticket',
            'class' => TicketType::class,
        ],
    ],
];
