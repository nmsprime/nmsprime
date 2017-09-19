<?php namespace Modules\Billingbase\Http\Controllers;

use Modules\BillingBase\Entities\NumberRange;
use Modules\BillingBase\Entities\CostCenter;

class NumberRangeController extends \BaseController {
	
	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new Numberrange;

		return array(
			array(
				'form_type' => 'text', 
				'name' => 'name', 
				'description' => 'Name'
			),
			array(
				'form_type' => 'text', 
				'name' => 'start', 
				'description' => 'Start'
			),
			array(
				'form_type' => 'text', 
				'name' => 'end', 
				'description' => 'End'
			),
			array(
				'form_type' => 'text', 
				'name' => 'prefix', 
				'description' => 'Prefix'
			),
			array(
				'form_type' => 'text', 
				'name' => 'suffix', 
				'description' => 'Suffix'
			),
			array(
				'form_type' => 'select', 
				'name' => 'type', 
				'description' => 'Type', 
				'value' => $model::getPossibleEnumValues('type')
			),
			array(
				'form_type' => 'select', 
				'name' => 'costcenter_id', 
				'description' => 'CostCenter', 
				'value' => $model->html_list(CostCenter::all(), 'name')
			),
		);
	}	
}
