<?php

namespace Modules\Ticketsystem\Http\Controllers;

use View;
use App\User;
use Illuminate\Support\Facades\Auth;
use Modules\Ticketsystem\Entities\Ticket;
use Modules\Ticketsystem\Entities\TicketType;

class TicketController extends \BaseController
{
    protected $many_to_many = [
        [
            'field' => 'users_ids',
        ],
        [
            'field' => 'tickettypes_ids',
        ],
    ];

    public function dashboard()
    {
        $title = 'Tickets Dashboard';
        $tickets['table'] = Auth::user()->tickets()->where('state', '=', 'New')->get();
        $tickets['total'] = count($tickets['table']);

        return View::make('ticketsystem::dashboard', $this->compact_prep_view(compact('title', 'tickets')));
    }

    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Ticket;
        }

        return [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Title'],
            ['form_type' => 'text', 'name' => 'duedate', 'description' => 'Due Date', 'space' => 1],
            ['form_type' => 'select', 'name' => 'state', 'description' => 'State', 'value' => Ticket::getPossibleEnumValues('state')],
            ['form_type' => 'select', 'name' => 'priority', 'description' => 'Priority', 'value' => Ticket::getPossibleEnumValues('priority')],
            ['form_type' => 'select', 'name' => 'tickettypes_ids[]', 'description' => 'Type',
                'value' => $model->html_list(TicketType::all(), 'name', true),
                'options' => ['multiple' => 'multiple'],
                'selected' => $model->html_list($model->tickettypes, 'name'), 'space' => 1, ],
            ['form_type' => 'select', 'name' => 'contract_id', 'description' => 'Contract', 'value' => $model->html_list(\DB::table('contract')->get(), ['number', 'firstname', 'lastname'], true, ' - ')],
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
            ['form_type' => 'text', 'name' => 'user_id', 'description' => 'Current user', 'value' => \Auth::user()->id, 'hidden' => 1],
            ['form_type' => 'select', 'name' => 'users_ids[]', 'description' => 'Assigned users',
                'value' => $model->html_list(User::all(), ['last_name', 'first_name'], false, ', '),
                'options' => ['multiple' => 'multiple'],
                'help' => trans('helper.assign_user'),
                'selected' => $model->html_list($model->users, ['last_name', 'first_name'], false, ', '), ],
        ];
    }
}
