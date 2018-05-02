<?php
namespace Modules\BillingBase\Http\Controllers;

use Modules\BillingBase\Entities\{CostCenter, NumberRange};

class NumberRangeController extends \BaseController {

	public function view_form_fields($model = null)
	{
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'text', 'name' => 'start', 'description' => 'Start'),
			array('form_type' => 'text', 'name' => 'end', 'description' => 'End'),
			array('form_type' => 'text', 'name' => 'prefix', 'description' => 'Prefix'),
			array('form_type' => 'text', 'name' => 'suffix', 'description' => 'Suffix'),
			array('form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'CostCenter', 'value' => $model->html_list(CostCenter::all(), 'name')),
			array('form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => NumberRange::get_types(), 'hidden' => 1),
		);
	}


	public function prepare_input($data)
	{
		$data['prefix'] = trim($data['prefix']);
		$data['suffix'] = trim($data['suffix']);

		return parent::prepare_input($data);
	}


	public function prepare_rules($rules, $data)
	{
		$rules['end'] .= $data['start'] ? "|min:".$data['start'] : '';

		return parent::prepare_rules($rules, $data);
	}

}
