<?php 
namespace Modules\Billingbase\Http\Controllers;

use Modules\BillingBase\Entities\Salesman;
use Modules\BillingBase\Entities\Product;

class SalesmanController extends \BaseModuleController {
	
    /**
     * defines the formular fields for the edit and create view
     */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new Salesman;

		$types = Product::getPossibleEnumValues('type');
		unset($types['Credit']);
		$types = implode(', ', $types);

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'firstname', 'description' => 'Firstname'),
			array('form_type' => 'text', 'name' => 'lastname', 'description' => 'Lastname'),
			array('form_type' => 'text', 'name' => 'commission', 'description' => 'Commission in %'),
			array('form_type' => 'text', 'name' => 'products', 'description' => 'Product List', 'help' => 'Add all Product types he gets provision for - possible: '.$types, 'options' => ['placeholder' => $types]),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
		);
	}

	
}