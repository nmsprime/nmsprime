<?php

namespace Modules\Ticketsystem\Http\Controllers;

use Bouncer;
use App\User;
use Modules\Ticketsystem\Entities\{Ticket, TicketType};

class TicketController extends \BaseController {

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
		if (!$model)
			$model = new Ticket;

		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Ticket title'),
			array('form_type' => 'text', 'name' => 'duedate', 'description' => 'Due Date', 'space' => 1),
			array('form_type' => 'select', 'name' => 'state', 'description' => 'Ticket state', 'value' => Ticket::getPossibleEnumValues('state')),
			array('form_type' => 'select', 'name' => 'priority', 'description' => 'Ticket priority', 'value' => Ticket::getPossibleEnumValues('priority')),
			array('form_type' => 'select', 'name' => 'tickettypes_ids[]', 'description' => 'Ticket type',
				'value' => $model->html_list(TicketType::all(), 'name', true),
				'options' => ['multiple' => 'multiple'],
				'selected' => $model->html_list($model->tickettypes, 'name'), 'space' => 1),
			array('form_type' => 'select', 'name' => 'contract_id', 'description' => 'Contract', 'value' => $model->html_list(\DB::table('contract')->get(), ['number', 'firstname', 'lastname'], true, ' - ')),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Ticket description'),
			array('form_type' => 'text', 'name' => 'user_id', 'description' => 'Current user', 'value' => \Auth::user()->id, 'hidden' => 1),
			array('form_type' => 'select', 'name' => 'users_ids[]', 'description' => 'Assigned users',
				'value' => $model->html_list(User::all(), ['last_name', 'first_name'], false, ', '),
				'options' => ['multiple' => 'multiple'],
				'help' => trans('helper.assign_user'),
				'selected' => $model->html_list($model->users, ['last_name', 'first_name'], false, ', ')),
		);
	}

}
