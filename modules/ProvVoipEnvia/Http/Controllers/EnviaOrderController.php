<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

/* use Modules\ProvVoipEnvia\Entities\EnviaOrders; */

class EnviaOrderController extends \BaseModuleController {

	/* protected $index_create_allowed = false; */

	/**
	 * defines the formular fields for the edit and create view
	 */
	public function get_form_fields($model = null)
	{

		// label has to be the same like column in sql table
		$ret = array(
			array('form_type' => 'text', 'name' => 'orderid', 'description' => 'Order ID', 'options' => ['readonly']),
			/* array('form_type' => 'text', 'name' => 'ordertype_id', 'description' => 'Ordertype', 'options' => ['readonly']), */
			array('form_type' => 'text', 'name' => 'ordertype', 'description' => 'Order type', 'options' => ['readonly']),
			/* array('form_type' => 'text', 'name' => 'orderstatus_id', 'description' => 'Orderstatus ID', 'options' => ['readonly']), */
			array('form_type' => 'text', 'name' => 'orderstatus', 'description' => 'Orderstatus', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'orderdate', 'description' => 'Orderdate', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'ordercomment', 'description' => 'Ordercomment', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'customerreference', 'description' => 'Envia customer reference', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'contractreference', 'description' => 'Envia contract reference', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'options' => ['readonly'], 'hidden' => '0'),
			array('form_type' => 'text', 'name' => 'contract', 'description' => 'Contract', 'value' => $model->contract->number, 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'phonenumber_id', 'description' => 'Phonenumber', 'options' => ['readonly']),
		);

		// order can be related to phonenumber (and contract) or to contract alone – the current order is maybe not bundled with a number, we have to catch this special case
		$phonenumber = $model->phonenumber;
		if (!is_null($phonenumber)) {
			$nr = $phonenumber->prefix_number.'/'.$phonenumber->number;
			array_push($ret, array('form_type' => 'text', 'name' => 'phonenumber', 'description' => 'Phonenumber', 'value' => $nr, 'options' => ['readonly']));
		}

		return $ret;
	}
}
