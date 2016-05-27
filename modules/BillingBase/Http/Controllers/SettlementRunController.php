<?php 
namespace Modules\Billingbase\Http\Controllers;


class SettlementRunController extends \BaseModuleController {

	public function view_form_fields($model = null)
	{

		return [
			array('form_type' => 'text', 'name' => 'year', 'description' => 'Year', 'hidden' => 'C', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'month', 'description' => 'Month', 'hidden' => 'C', 'options' => ['readonly']),
			// array('form_type' => 'text', 'name' => 'path', 'description' => 'Path'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
			array('form_type' => 'checkbox', 'name' => 'verified', 'description' => 'Verified', 'hidden' => 'C'),
		];
	}

	public function prepare_input($data)
	{
		return parent::prepare_input($data);
	}


	// public function edit($id)
	// {
	// 	return \View::make('billingbase::settlementrun', $this->compact_prep_view(compact('')));
	// 	// return parent::edit();
	// }



	public function store($redirect = true)
	{
		\Artisan::call('billing:accounting');
		return parent::store();
	}

}