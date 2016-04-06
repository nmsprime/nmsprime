<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\EnviaOrder;
use Modules\ProvVoip\Entities\PhonenumberManagement;

class EnviaOrderController extends \BaseModuleController {

	/* protected $index_create_allowed = false; */

	/**
	 * defines the formular fields for the edit and create view
	 */
	public function get_form_fields($model = null)
	{

		// make order_id fillable on create => so man can add an order created at the web GUI to keep data consistent
		if (!$model->exists) {
			$order_id = array('form_type' => 'text', 'name' => 'orderid', 'description' => 'Order ID');
		}
		else {
			$order_id = array('form_type' => 'text', 'name' => 'orderid', 'description' => 'Order ID', 'options' => ['readonly']);
		}



		// label has to be the same like column in sql table
		$ret = array(
			$order_id,
			/* array('form_type' => 'text', 'name' => 'ordertype_id', 'description' => 'Ordertype', 'options' => ['readonly']), */
			array('form_type' => 'text', 'name' => 'ordertype', 'description' => 'Order type', 'options' => ['readonly']),
			/* array('form_type' => 'text', 'name' => 'orderstatus_id', 'description' => 'Orderstatus ID', 'options' => ['readonly']), */
			array('form_type' => 'text', 'name' => 'orderstatus', 'description' => 'Orderstatus', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'orderdate', 'description' => 'Orderdate', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'ordercomment', 'description' => 'Ordercomment', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'customerreference', 'description' => 'Envia customer reference', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'contractreference', 'description' => 'Envia contract reference', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'options' => ['readonly'], 'hidden' => '0'),
			/* array('form_type' => 'text', 'name' => 'contract', 'description' => 'Contract', 'value' => $model->contract->number, 'options' => ['readonly']), */
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


	public function create() {

		$phonenumbermanagement_id = \Input::get('phonenumbermanagement_id', null);
		$phonenumber_id = \Input::get('phonenumber_id', null);
		$contract_id = \Input::get('contract_id', null);

		// if order_id is given: all is fine => call parent
		// in this case we take for sure that the caller is is either contract=>create_envia_order or a redirected phonenumbermanagement=>create_envia_order
		if (!is_null($contract_id)) {
			return parent::create();
		}

		// else: calculate contract_id and (if possible) phonenumber_id
		if (is_null($phonenumbermanagement_id)) {
			throw new \RuntimeException("Order has to be related to contract or to phonenumbermanagement");
		}

		$phonenumbermanagement = PhonenumberManagement::findOrFail($phonenumbermanagement_id);

		// build new parameter set (this is: attach contract_id and phonenumber_id
		$params = \Input::all();
		$params['phonenumber_id'] = $phonenumbermanagement->phonenumber->id;
		$params['contract_id'] = $phonenumbermanagement->phonenumber->mta->modem->contract->id;
		$params['contractreference'] = $phonenumbermanagement->phonenumber->mta->modem->contract->contract_external_id;
		$params['customerreference'] = $phonenumbermanagement->phonenumber->mta->modem->contract->customer_external_id;

		// call create again with extended parameters
		return \Redirect::action('\Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderController@create', $params);
	}

	/**
	 * Overwrite base function => before creation in database we have to check if order exists at envia!
	 *
	 * @author Patrick Reichel
	 */
	public function store() {

		// call parent and store return
		// so authentication is done!
		$parent_return = parent::store();

		// if previous action is not create: passthrough parent return
		if (!\Str::contains(\URL::previous(), 'EnviaOrder/create?')) {
			return $parent_return;
		}

		// else redirect to check newly created order against Envia API
		$order_id = \Input::get('orderid');
		$params = array(
			'job' => 'order_get_status',
			'order_id' => $order_id,
			'really' => 'true',
			'origin' => urlencode(\URL::previous()),
		);

		return \Redirect::action('\Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@request', $params);
	}


	/**
	 * Overwrite delete function => we have to cancel an order also against the envia API
	 *
	 * @author Patrick Reichel
	 */
	public function destroy($id) {

		// TODO: outsource to own controller/method?
		try {
			// this needs view rights; edit rights are checked in store/update methods!
			$this->_check_permissions("view");
			$this->_check_permissions("view", "Modules\ProvVoipEnvia\Entities\ProvVoipEnvia");
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
