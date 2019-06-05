<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Input;
use Bouncer;
use Illuminate\Support\Facades\View;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvVoip\Entities\Phonenumber;
use Modules\ProvVoipEnvia\Entities\EnviaOrder;
use Modules\ProvVoip\Entities\PhonenumberManagement;

class EnviaOrderController extends \BaseController
{
    protected $index_create_allowed = false;
    protected $index_delete_allowed = false;

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        $init_values = [];
        $phonenumber_id = null;
        $contract_id = null;
        $modem_id = null;
        $related_id = null;

        // make order_id fillable on create => so man can add an order created at the web GUI to keep data consistent
        if (! $model->exists) {
            $order_id = ['form_type' => 'text', 'name' => 'orderid', 'description' => 'Order ID'];

            // order can be related to phonenumber and/or modem and/or contract
            // get the contract (has to be given; watch create()
            $contract_id = Input::get('contract_id', null);
            $init_values['contract_id'] = $contract_id;

            // try to get modem (can be given)
            $modem_id = Input::get('modem_id', null);
            if (boolval($modem_id)) {
                $init_values['modem_id'] = $modem_id;
                if (! $modem = Modem::find($modem_id)) {
                    return [];
                }
                $init_values['contract_id'] = $modem->contract->id;
            }

            // try to get phonenumber (can be given)
            $phonenumber_id = Input::get('phonenumber_id', null);
            if (boolval($phonenumber_id)) {
                $init_values['phonenumber_id'] = $phonenumber_id;
                if (! $phonenumber = Phonenumber::find($phonenumber_id)) {
                    return [];
                }
                $init_values['modem_id'] = $phonenumber->mta->modem->id;
                $init_values['contract_id'] = $phonenumber->mta->modem->contract->id;
            }
        } else {

            // try to get related order
            // this can also be deleted!
            $related_id = $model->related_order_id;
            if (boolval($related_id)) {
                $order_related = EnviaOrder::withTrashed()->find($related_id);
                $init_values['related_order_id_show'] = $order_related->orderid.' <i>(DB ID: '.$order_related->id.')</i>';
                $init_values['related_order_type'] = $order_related->ordertype;
                $init_values['related_order_created_at'] = $order_related->created_at;
                $init_values['related_order_updated_at'] = $order_related->updated_at;
                if (boolval($order_related->deleted_at)) {
                    $init_values['related_order_deleted_at'] = $order_related->deleted_at;
                }
            }

            // try to get related phonenumbers
            $tmp_nr = $model->phonenumbers;
            if (is_null($tmp_nr)) {
                $phonenumber_ids = '';
            } else {
                $phonenumber_ids = $tmp_nr->implode('id', ', ');
            }
            $init_values['phonenumber_id'] = $phonenumber_ids;

            $order_id = ['form_type' => 'text', 'name' => 'orderid', 'description' => 'Order ID', 'options' => ['readonly']];
        }

        // label has to be the same like column in sql table
        $ret_tmp = [
            $order_id,
            ['form_type' => 'text', 'name' => 'created_at', 'description' => 'Created at', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'updated_at', 'description' => 'Last status update', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'last_user_interaction', 'description' => 'Last user interaction', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'method', 'description' => 'Method', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'ordertype_id', 'description' => 'Ordertype ID', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'ordertype', 'description' => 'Ordertype', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'orderstatus_id', 'description' => 'Orderstatus ID', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'orderstatus', 'description' => 'Orderstatus', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'orderdate', 'description' => 'Orderdate', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'ordercomment', 'description' => 'Ordercomment', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'customerreference', 'description' => 'envia TEL customer reference', 'options' => ['readonly'], 'hidden' => 'C'],
            ['form_type' => 'text', 'name' => 'contractreference', 'description' => 'envia TEL contract reference', 'options' => ['readonly'], 'hidden' => 'C', 'space' => '1'],
            ['form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract ID', 'options' => ['readonly'], 'hidden' => 1],
            ['form_type' => 'text', 'name' => 'modem_id', 'description' => 'Modem ID', 'options' => ['readonly'], 'hidden' => 1],
            ['form_type' => 'text', 'name' => 'phonenumber_id', 'description' => 'Phonenumber ID', 'options' => ['readonly'], 'hidden' => 1],
        ];

        // add information to related order (e.g. for “Stornierung”) if exists
        if (boolval($related_id)) {
            // this fields are for information only => they have to be removed in observer on updating
            // attention: related order can also be deleted!
            array_push($ret_tmp, ['form_type' => 'text', 'name' => 'related_order_id_show', 'description' => 'Related order ID', 'options' => ['readonly'], 'hidden' => 'C']);
            array_push($ret_tmp, ['form_type' => 'text', 'name' => 'related_order_type', 'description' => 'Related order type', 'options' => ['readonly'], 'hidden' => 'C']);
            array_push($ret_tmp, ['form_type' => 'text', 'name' => 'related_order_created_at', 'description' => 'Related order created', 'options' => ['readonly'], 'hidden' => 'C']);
            array_push($ret_tmp, ['form_type' => 'text', 'name' => 'related_order_updated_at', 'description' => 'Related order last updated', 'options' => ['readonly'], 'hidden' => 'C']);
            if (array_key_exists('related_order_deleted_at', $init_values)) {
                array_push($ret_tmp, ['form_type' => 'text', 'name' => 'related_order_deleted_at', 'description' => 'Related order deleted', 'options' => ['readonly'], 'hidden' => 'C']);
            }
        }

        // add init values if set
        $ret = [];
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

    public function create()
    {
        $phonenumbermanagement_id = Input::get('phonenumbermanagement_id', null);
        $phonenumber_id = Input::get('phonenumber_id', null);
        $modem_id = Input::get('modem_id', null);
        $contract_id = Input::get('contract_id', null);

        // if contract_id is given: all is fine => call parent
        // in this case we take for sure that the caller is is either contract=>create_envia_order or a redirected phonenumbermanagement=>create_envia_order
        if (! is_null($contract_id)) {
            if (! Contract::find($contract_id)) {
                $this->edit_view_save_button = false;
                \Session::push('tmp_info_above_form', "Cannot create EnviaOrder – contract $contract_id does not exist");
            }

            return parent::create();
        }

        // build new parameter set (this is: attach contract_id, modem_id and phonenumber_id)
        // first: preserve the parent (the first _GET param given) as this is needed within BaseViewController
        // so we put the complete array in front of new params
        $params = $_GET;

        // then we add all possibly given input values (this is e.g. the _token to avoid CSRF attacks)
        foreach (Input::all() as $key => $value) {
            if (! array_key_exists($key, $params)) {
                $params[$key] = $value;
            }
        }

        // if no contract_id has been given: calculate contract_id and (if possible) modem_id and/or phonenumber_id
        if (is_null($modem_id) && is_null($phonenumbermanagement_id)) {
            $this->edit_view_save_button = false;
            \Session::push('tmp_info_above_form', 'Cannot create EnviaOrder – neither contract_id nor modem_id nor phonenumbermanagement_id given.');

            return parent::create();
        }

        if (! is_null($phonenumbermanagement_id)) {
            if (! $phonenumbermanagement = PhonenumberManagement::find($phonenumbermanagement_id)) {
                $this->edit_view_save_button = false;
                \Session::push('tmp_info_above_form', "Cannot create EnviaOrder – PhonenumberManagement $phonenumbermanagement_id does not exist");

                return parent::create();
            }
            $params['phonenumber_id'] = $phonenumbermanagement->phonenumber->id;
            $params['modem_id'] = $phonenumbermanagement->phonenumber->mta->modem->id;
            $params['contract_id'] = $phonenumbermanagement->phonenumber->mta->modem->contract->id;
            $params['contractreference'] = $phonenumbermanagement->phonenumber->mta->modem->contract_external_id;
            $params['customerreference'] = $phonenumbermanagement->phonenumber->mta->modem->contract->customer_external_id;
        } elseif (! is_null($modem_id)) {
            if (! $modem = Modem::find($modem_id)) {
                $this->edit_view_save_button = false;
                \Session::push('tmp_info_above_form', "Cannot create EnviaOrder – Modem $modem_id does not exist");

                return parent::create();
            }
            $params['phonenumber_id'] = null;
            $params['modem_id'] = $modem->id;
            $params['contract_id'] = $modem->contract->id;
            $params['contractreference'] = $modem->contract_external_id;
            $params['customerreference'] = $modem->contract->customer_external_id;
        }

        // finally we add the related ids
        $params['method'] = 'manually';

        // call create again with extended parameters
        return \Redirect::action('\Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderController@create', $params);
    }

    /**
     * Overwrite base method.
     *
     * Here we inject the following data:
     *	- information about needed/possible user actions
     *	- mailto: link to envia TEL support as additional data
     *
     * @author Patrick Reichel
     */
    protected function _get_additional_data_for_edit_view($model)
    {
        $additional_data = [
            'user_actions' => $model->get_user_action_information(),
            'mailto_links' => $model->get_mailto_links(),
        ];

        return $additional_data;
    }

    /**
     * Call this method to mark an order as solved.
     * (that is: update last_user_interaction in database to current date without touching updated_at)
     *
     * After triggering the action this will redirect to edit
     *
     * @author Patrick Reichel
     */
    public function mark_solved($id)
    {
        if (Bouncer::can('update', EnviaOrder::class) &&
            Bouncer::can('update', 'Modules\ProvVoipEnvia\Entities\ProvVoipEnvia')) {
            $model = EnviaOrder::findOrFail($id);
            $model->mark_as_solved();
        } else {
            throw new AuthException('Not allowed to mark Envia Order as solved. Please ask your Administrator');
        }

        return \Redirect::back();
    }

    /**
     * Before showing the edit form we update the order using envia api
     *
     * @author Patrick Reichel
     */
    public function edit($id)
    {

        // call parent and store return
        // so authentication is done!
        $parent_return = parent::edit($id);

        // if already updated against envia TEL API: show edit form
        if (Input::get('recently_updated', false)) {
            return $parent_return;
        }

        // else redirect to update order against envia TEL
        $params = [
            'job' => 'order_get_status',
            'order_id' => EnviaOrder::findOrFail($id)->orderid,
            'really' => 'true',
            'origin' => urlencode(\URL::current()),
            'instant_redirect' => true,
        ];

        return \Redirect::action('\Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@request', $params);
    }

    /**
     * Overwrite base function => before creation in database we have to check if order exists at envia!
     *
     * @author Patrick Reichel
     */
    public function store($redirect = true)
    {

        // call parent and store return
        // so authentication is done!
        $parent_return = parent::store($redirect);

        // if previous action is not create: passthrough parent return
        if (! \Str::contains(\URL::previous(), 'EnviaOrder/create?')) {
            return $parent_return;
        }

        // else redirect to check newly created order against envia TEL API
        $order_id = Input::get('orderid');
        $params = [
            'job' => 'order_get_status',
            'order_id' => $order_id,
            'really' => 'true',
            'origin' => urlencode(\URL::previous()),
        ];

        return \Redirect::action('\Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@request', $params);
    }

    /**
     * Overwrite delete function => we have to cancel an order also against the envia API
     *
     * @author Patrick Reichel
     */
    public function destroy($id)
    {
        if (Bouncer::cannot('delete', EnviaOrder::class) &&
            Bouncer::cannot('view', 'Modules\ProvVoipEnvia\Entities\ProvVoipEnvia')) {
            throw new AuthException('Access to model EnviaOrder not allowed for user '.Auth::user()->login_name.'.');
        }
        // get all orders to be canceled
        $orders = [];
        if ($id == 0) {
            // bulk deletion is not supported (yet?)
            $ids = Input::all()['ids'];
            if (count($ids) > 1) {
                // TODO: make a nicer output
                echo '<h3>Error: Cannot cancel more than one order per time</h3>';
                echo '<a href="javascript:history.back()" target="_self">Back to previous page</a>';

                return;
            }
            // delete (attention: database ids are the keys of the input array)
            $ids = array_keys($ids);
            $id = array_pop($ids);
            $order = static::get_model_obj()->findOrFail($id);
        } else {
            $order = static::get_model_obj()->findOrFail($id);
        }

        $params = [
            'job' => 'order_cancel',
            'order_id' => $order->orderid,
            'origin' => urlencode(\URL::previous()),
        ];

        return \Redirect::action('\Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@request', $params);
    }

    /**
     * Overwrite BaseController method => allow some fields to be NULL in database if not set
     * Otherwise we get entries like 0000-00-00, which cause crashes on validation rules in case of update
     *
     * @author Patrick Reichel
     */
    protected function prepare_input($data)
    {
        $data = parent::prepare_input($data);

        $nullable_fields = [
            'ordertype_id',
            'ordertype',
            'orderstatus_id',
            'orderstatus',
            'orderdate',
            'ordercomment',
            'related_order_id',
            'customerreference',
            'contractreference',
            'contract_id',
            'last_user_interaction',
        ];
        $data = $this->_nullify_fields($data, $nullable_fields);

        return $data;
    }
}
