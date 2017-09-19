<?php

namespace Modules\Ticketsystem\Http\Controllers;

use App\Authuser;
use Modules\Ticketsystem\Entities\Assignee;

class AssigneeController extends \BaseController {
	
	protected $index_create_allowed = false;

	protected $index_delete_allowed = false;

	public function view_form_fields($model = null)
	{
		if (!$model) {
			$model = new Assignee;
		}

		return array(
			array(
				'form_type' => 'select', 
				'name' => 'user_id', 
				'description' => 'Assignees', 
//				'value' => $model->html_list(Authuser::all(), array('last_name', 'first_name'), false, ', ')
				'value' => $model->html_list($model->get_assignees(), array('last_name', 'first_name'), false, ', ')
			),
			array(
				'form_type' => 'text', 
				'name' => 'ticket_id', 
				'hidden' => 1,
			),
		);
	}
}
