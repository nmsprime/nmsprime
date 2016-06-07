<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\EnviaOrder;
use Modules\ProvVoip\Entities\PhonenumberManagement;
use Modules\ProvVoip\Entities\Phonenumber;

class EnviaOrderController extends \BaseController {

	/* protected $index_create_allowed = false; */

	/**
	 * defines the formular fields for the edit and create view
	 */
	public function view_form_fields($model = null)
	{

		$init_values = array();
		$phonenumber_id = null;
		$contract_id = null;

		// make order_id fillable on create => so man can add an order created at the web GUI to keep data consistent
		if (!$model->exists) {

			$order_id = array('form_type' => 'text', 'name' => 'orderid', 'description' => 'Order ID');

			// order can be related to phonenumber (and contract) or to contract alone
			// get the contract (has to be given; watch create()
			$contract_id = \Input::get('contract_id', null);
			if (!is_null($contract_id)) {
				$init_values['contract_id'] = $contract_id;
			}
			else {
				throw new \InvalidArgumentException('Order at least has to be related to a contract, but could not get a contract id');
			}

			// try to get phonenumber (can be given)
			$phonenumber_id = \Input::get('phonenumber_id', null);
			if (!is_null($phonenumber_id)) {
				$init_values['phonenumber_id'] = $phonenumber_id;
				$phonenumber = Phonenumber::findOrFail($phonenumber_id);
				$init_values['contract_id'] = $phonenumber->mta->modem->contract->id;
			}


		}
		else {
			$order_id = array('form_type' => 'text', 'name' => 'orderid', 'description' => 'Order ID', 'options' => ['readonly']);
		}


		// label has to be the same like column in sql table
		$ret_tmp = array(
			$order_id,
			array('form_type' => 'text', 'name' => 'ordertype_id', 'description' => 'Ordertype ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'ordertype', 'description' => 'Ordertype', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'orderstatus_id', 'description' => 'Orderstatus ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'orderstatus', 'description' => 'Orderstatus', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'orderdate', 'description' => 'Orderdate', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'ordercomment', 'description' => 'Ordercomment', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'customerreference', 'description' => 'Envia customer reference', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'contractreference', 'description' => 'Envia contract reference', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'phonenumber_id', 'description' => 'Phonenumber ID', 'options' => ['readonly']),
		);


		// add init values if set
		$ret = array();
		foreach ($ret_tmp as $elem) {

			/* echo '<pre>'; */
			/* print_r($elem); */
			/* echo '</pre>'; */

			if (array_key_exists($elem['name'], $init_values)) {
				$elem['init_value'] = $init_values[$elem['name']];
			}
			array_push($ret, $elem);
		}

		return $ret;
	}

	public function create() {

		$phonenumbermanagement_id = \Input::get('phonenumbermanagement_id', null);
		$phonenumber_id = \Input::get('phonenumber_id', null);
		$contract_id = \Input::get('contract_id', null);

		// if contract_id is given: all is fine => call parent
		// in this case we take for sure that the caller is is either contract=>create_envia_order or a redirected phonenumbermanagement=>create_envia_order
		if (!is_null($contract_id)) {
			return parent::create();
		}

		// else: calculate contract_id and (if possible) phonenumber_id
		if (is_null($phonenumbermanagement_id)) {
			throw new \RuntimeException("Order has to be related to contract or to phonenumbermanagement");
		}

		$phonenumbermanagement = PhonenumberManagement::findOrFail($phonenumbermanagement_id);

		// build new parameter set (this is: attach contract_id and phonenumber_id)
		// first: preserve the parent (the first _GET param given) as this is needed within BaseViewController
		// so we put the complete array in front of new params
		$params = $_GET;

		// then we add all possibly given input values (this is e.g. the _token to avoid CSRF attacks)
		foreach (\Input::all() as $key => $value) {
			if (!array_key_exists($key, $params)) {
				$params[$key] = $value;
			}
		}

		// finally we add the related ids
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
	public function store($redirect=true) {

		// call parent and store return
		// so authentication is done!
		$parent_return = parent::store($redirect);

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

		// check if user has the right to perform actions against Envia API
		\App\Http\Controllers\BaseAuthController::auth_check('view', \NamespaceController::get_model_name());
		\App\Http\Controllers\BaseAuthController::auth_check('view', 'Modules\ProvVoipEnvia\Entities\ProvVoipEnvia');


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
			$order = static::get_model_obj()->findOrFail($id);
		}
		else {
			$order = static::get_model_obj()->findOrFail($id);
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
