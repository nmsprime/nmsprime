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
				'description' => \App\Http\Controllers\BaseViewController::translate_view('Start', 'Numberrange')
			),
			array(
				'form_type' => 'text', 
				'name' => 'end', 
				'description' => \App\Http\Controllers\BaseViewController::translate_view('End', 'Numberrange')
			),
			array(
				'form_type' => 'text', 
				'name' => 'prefix', 
				'description' => \App\Http\Controllers\BaseViewController::translate_view('Prefix', 'Numberrange')
			),
			array(
				'form_type' => 'text', 
				'name' => 'suffix', 
				'description' => \App\Http\Controllers\BaseViewController::translate_view('Suffix', 'Numberrange')
			),
			array(
				'form_type' => 'select', 
				'name' => 'type', 
				'description' => 'Type', 
				'value' => $this->get_types($model),
			),
			array(
				'form_type' => 'text',
				'name' => 'costcenter_id',
				'hidden' => 1,
			),
		);
	}

	protected function get_types($model)
	{
		$ret = [];
		$types = $model::getPossibleEnumValues('type');

		foreach ($types as $key => $name) {
			$ret[$key] = \App\Http\Controllers\BaseViewController::translate_view($name, 'Numberrange_Type');
		}

		return $ret;
	}
}
