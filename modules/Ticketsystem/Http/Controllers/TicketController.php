<?php

namespace Modules\Ticketsystem\Http\Controllers;

use App\User;
use Modules\Ticketsystem\Entities\Ticket;
use Modules\Ticketsystem\Entities\TicketType;

class TicketController extends \BaseController
{
    protected $edit_left_md_size = 6;
    protected $edit_right_md_size = 6;

    protected $many_to_many = [
        [
            'field' => 'users_ids',
        ],
        [
            'field' => 'tickettypes_ids',
        ],
    ];

    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Ticket();
        }

        $top = [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Title'],
            ['form_type' => 'text', 'name' => 'duedate', 'description' => 'Due Date', 'space' => 1],
            ['form_type' => 'select', 'name' => 'state', 'description' => 'State', 'value' => Ticket::getPossibleEnumValues('state')],
            ['form_type' => 'select', 'name' => 'priority', 'description' => 'Priority', 'value' => Ticket::getPossibleEnumValues('priority')],
            ['form_type' => 'select', 'name' => 'tickettypes_ids[]', 'description' => 'Type',
                'value' => $model->html_list(TicketType::all(), 'name', true),
                'options' => ['multiple' => 'multiple'],
                'selected' => $model->html_list($model->tickettypes, 'name'), 'space' => 1, ],
            ['form_type' => 'select', 'name' => 'contract_id', 'description' => 'Contract', 'value' => $model->html_list(\DB::table('contract')->get(), ['number', 'firstname', 'lastname'], true, ' - ')],
        ];

        $mid = [];
        if (\Module::collections()->has('PropertyManagement')) {
            $mid = [
                ['form_type' => 'select', 'name' => 'contact_id', 'description' => 'Contact', 'value' => $model->html_list(\DB::table('contact')->get(), ['firstname1', 'lastname1', 'company'], true, ' - ')],
                ['form_type' => 'select', 'name' => 'apartment_id', 'description' => 'Apartment', 'value' => $model->html_list(\DB::table('apartment')->get(), ['realty_id', 'number', 'floor'], true, ' - ')],
                ['form_type' => 'select', 'name' => 'realty_id', 'description' => 'Realty', 'value' => $model->html_list(\DB::table('realty')->get(), ['name', 'city', 'street', 'house_nr'], true, ' - ')],
            ];
        }

        $bot = [
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
            ['form_type' => 'text', 'name' => 'user_id', 'description' => 'Current user', 'value' => \Auth::user()->id, 'hidden' => 1],
            ['form_type' => 'select', 'name' => 'users_ids[]', 'description' => 'Assigned users',
                'value' => $model->html_list(User::all(), ['last_name', 'first_name'], false, ', '),
                'options' => ['multiple' => 'multiple'],
                'help' => trans('helper.assign_user'),
                'selected' => $model->html_list($model->users, ['last_name', 'first_name'], false, ', '), ],
        ];

        return array_merge($top, $mid, $bot);
    }

    public function index()
    {
        Ticket::storeIndexFilterIntoSession();

        return parent::index();
    }
}
