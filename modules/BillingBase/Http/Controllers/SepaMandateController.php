<?php
namespace Modules\BillingBase\Http\Controllers;

use Nwidart\Modules\Routing\Controller;
use Modules\BillingBase\Entities\{CostCenter, SepaAccount, SepaMandate, BillingBase};

class SepaMandateController extends \BaseController {

	protected $index_create_allowed = false;

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new SepaMandate;

		if (!$model->exists)
		{
			$contract = \Modules\ProvBase\Entities\Contract::find(\Input::get('contract_id'));
			$model->contract = $contract;

			$model->reference = self::build_mandate_ref($model);
			$model->signature_date = date('Y-m-d');
			$model->sepa_holder = $contract->firstname.' '.$contract->lastname;
			$model->sepa_valid_from = date('Y-m-d');
		}

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'reference', 'description' => 'Reference Number', 'create' => '1'),
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'hidden' => '1'),
			array('form_type' => 'text', 'name' => 'sepa_holder', 'description' => 'Bank Account Holder'),
			array('form_type' => 'text', 'name' => 'sepa_iban', 'description' => 'IBAN'),
			array('form_type' => 'text', 'name' => 'sepa_bic', 'description' => 'BIC'),
			array('form_type' => 'text', 'name' => 'sepa_institute', 'description' => 'Bank Institute', 'space' => 1),
			array('form_type' => 'text', 'name' => 'signature_date', 'description' => 'Date of Signature', 'options' => ['placeholder' => 'YYYY-MM-DD']),
			array('form_type' => 'text', 'name' => 'sepa_valid_from', 'description' => 'Valid from', 'options' => ['placeholder' => 'YYYY-MM-DD']),
			array('form_type' => 'text', 'name' => 'sepa_valid_to', 'description' => 'Valid to', 'options' => ['placeholder' => 'YYYY-MM-DD'], 'space' => 1),
			array('form_type' => 'checkbox', 'name' => 'disable', 'description' => 'Disable temporary', 'value' => '1'),
			array('form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'CostCenter', 'value' => $model->html_list(CostCenter::all(), 'name', true), 'help' => trans('helper.sm_cc')),
			array('form_type' => 'select', 'name' => 'state', 'description' => 'State', 'value' => SepaMandate::getPossibleEnumValues('state'), 'space' => 1),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
		);
	}


	public function prepare_input($data)
	{
		$data['sepa_bic'] = $data['sepa_bic'] ? : SepaAccount::get_bic($data['sepa_iban']);
		$data['sepa_bic'] = strtoupper(str_replace(' ', '' ,$data['sepa_bic']));
		$data['sepa_iban'] = strtoupper(str_replace(' ', '' ,$data['sepa_iban']));
		// $data['signature_date'] = $data['signature_date'] ? : date('Y-m-d');

		$data = parent::prepare_input($data);

		// set this to null if no value is given
		$data = $this->_nullify_fields($data, ['sepa_valid_to']);

		return $data;
	}


	public function prepare_rules($rules, $data)
	{
		$rules['sepa_bic'] .= $data['sepa_bic'] ? '' : '|required';

		return parent::prepare_rules($rules, $data);
	}


	/**
	 * Replaces placeholders from in Global Config defined mandate reference template with values of mandate or the related contract
	 */
	public static function build_mandate_ref($mandate)
	{
		$template = BillingBase::first()->mandate_ref_template;

		if (!$template || (strpos($template, '{') === false))
			return $mandate->contract->number;

		// replace placeholder with values
		preg_match_all('/(?<={)[^}]*(?=})/', $template, $matches);

		foreach ($matches[0] as $key)
		{
			if (array_key_exists($key, $mandate->contract['attributes']))
				$template = str_replace('{'.$key.'}', $mandate->contract['attributes'][$key], $template);
			else if (array_key_exists($key, $mandate['attributes']))
				$template = str_replace('{'.$key.'}', $mandate['attributes'][$key], $template);
		}

		return $template;
	}
}
