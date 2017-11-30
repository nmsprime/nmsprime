<?php namespace Modules\Ticketsystem\Http\Controllers;

use Modules\Ticketsystem\Entities\Ticket;
use Modules\Ticketsystem\Entities\Assignee;

class TicketController extends \BaseController {

	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new Ticket;

		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Ticket title'),
			array('form_type' => 'select', 'name' => 'type', 'description' => 'Ticket type', 'value' => Ticket::getPossibleEnumValues('type')),
			array('form_type' => 'select', 'name' => 'state', 'description' => 'Ticket state', 'value' => Ticket::getPossibleEnumValues('state')),
			array('form_type' => 'select', 'name' => 'priority', 'description' => 'Ticket priority', 'value' => Ticket::getPossibleEnumValues('priority')),
			array('form_type' => 'select', 'name' => 'contract_id', 'description' => 'Contract', 'value' => $model->html_list(\DB::table('contract')->get(), ['number', 'firstname', 'lastname'], true, ' - ')),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Ticket description'),
			array('form_type' => 'text', 'name' => 'user_id', 'description' => 'Current user', 'init_value' => \Auth::user()->id, 'hidden' => 1),
			array('form_type' => 'select', 'name' => 'assigned_user_id[]', 'description' => 'Assign user',
					'value' => $model->html_list($model->not_assigned_users(), ['last_name', 'first_name'], false, ', '),
					'options' => array('multiple' => 'multiple',), 'help' => trans('helper.assign_user')),
		);
	}

	protected function prepare_input($data)
	{
		$data['assigned_user_id'] = isset($data['assigned_user_id']) ? implode(';', $data['assigned_user_id']) : null;

		return parent::prepare_input($data);
	}

}
