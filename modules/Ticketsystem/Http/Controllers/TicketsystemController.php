<?php

namespace Modules\Ticketsystem\Http\Controllers;

use Modules\Ticketsystem\Entities\Ticket;

class TicketsystemController extends \BaseController
{
    public function index()
    {
        $title = 'Tickets Dashboard';
        $tickets = self::dashboardData();

        return \View::make('ticketsystem::index', $this->compact_prep_view(compact('title', 'tickets')));
    }

    public static function dashboardData()
    {
        $tickets['total'] = Ticket::where('state', 'New')->count();
        $tickets['table'] = auth()->user()->tickets()->where('state', 'New')->get();
        $tickets['own'] = count($tickets['table']);

        return $tickets;
    }
}
