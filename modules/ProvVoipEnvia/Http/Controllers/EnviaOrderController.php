<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\EnviaOrder;

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

	/**
	 * Overwrite delete function => we have to cancel an order also against the envia API
	 *
	 * @author Patrick Reichel
	 */
	public function destroy($id) {

		try {
			// this needs view rights; edit rights are checked in store/update methods!
			$this->_check_permissions("view");
		}
		catch (PermissionDeniedError $ex) {
			return View::make('auth.denied', array('error_msg' => $ex->getMessage()));
		}

		// get all orders to be canceled
		$orders = array();
		if ($id == 0)
		{
			// bulk deletion is not supported (yet?)
			$ids = \Input::all()['ids'];
			if (count($ids) > 1) {
				// TODO: make a nicer output
				echo "<h3>Error: Cannot cancel more than one order per time</h3>";
				echo '<a href="javascript:history.back()" target="_self">Back to previous page</a>';
				return;
			}
			// delete (attention: database ids are the keys of the input array)
			$ids = array_keys($ids);
			$id = array_pop($ids);
			$order = $this->get_model_obj()->findOrFail($id);
		}
		else {
			$order = $this->get_model_obj()->findOrFail($id);
		}

		$params = array(
			'job' => 'order_cancel',
			'order_id' => $order->orderid,
			/* 'origin' => urlencode(\Request::getUri()), */
			'origin' => urlencode(\URL::previous()),
		);

		return \Redirect::action('\Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@request', $params);
	}

}
