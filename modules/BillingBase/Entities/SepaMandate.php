<?php

namespace Modules\BillingBase\Entities;
use Modules\ProvBase\Entities\Contract;
use Digitick\Sepa\PaymentInformation;

use DB;
use Storage;

class SepaMandate extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'sepamandate';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'signature_date' 	=> 'date',
			'sepa_iban' 		=> 'required|iban',
			'sepa_bic' 			=> 'bic',			// see SepaMandateController@prep_rules
			// 'sepa_institute' 	=> ,
			'sepa_valid_from' 	=> 'date',
			'sepa_valid_to'		=> 'dateornull'
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function view_headline()
	{
		return 'SEPA Mandate';
	}

	public static function view_icon()
	{
		return '<i class="fa fa-handshake-o"></i>';
	}


	// AJAX Index list function
	// generates datatable content and classes for model
	public function view_index_label()
	{
		$bsclass = $this->get_bsclass();
		$valid_to = $this->sepa_valid_to ? ' - '.$this->sepa_valid_to : '';

		return ['table' => $this->table,
				'index_header' => [$this->table.'.sepa_holder', $this->table.'.sepa_valid_from', $this->table.'.sepa_valid_to', $this->table.'.reference'],
				'bsclass' => $bsclass,
				'order_by' => ['0' => 'asc'],
				'header' =>  $this->sepa_iban];
	}


	public function get_bsclass()
	{
		$bsclass = 'success';

		if ( isset($this->created_at) && ($this->get_start_time() > strtotime(date('Y-m-d'))) && !$this->check_validity('Now'))
			$bsclass = 'danger';

		return $bsclass;
	}


	public function view_belongs_to ()
	{
		return $this->contract;
	}


	/**
	 * Relationships:
	 */
	public function contract ()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Contract', 'contract_id');
	}

	public function costcenter ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\CostCenter');
	}


	/*
	 * Init Observers
	 */
	public static function boot()
	{
		SepaMandate::observe(new SepaMandateObserver);
		parent::boot();
	}


	/*
	 * Other Functions
	 */


	/**
	 * Update SEPA-Mandate status during SettlementRun (accountingCommand) if it changes
	 */
	public function update_status()
	{
		$end  = $this->get_end_time();
		$ends = $end && ($end < strtotime('first day of next month'));

		$changed = false;

		if ($this->state == PaymentInformation::S_FIRST) {
			$this->state == $ends ? PaymentInformation::S_ONEOFF : PaymentInformation::S_RECURRING;
			$changed = true;
		}

		else if ($ends) {
			$this->state = PaymentInformation::S_FINAL;
			$changed = true;
		}

		if ($changed)
			$this->save();
	}

	/**
	 * Returns start time of item - Note: sepa_valid_from field has higher priority than created_at
	 *
	 * @return integer 		time in seconds after 1970
	 */
	public function get_start_time()
	{
		$date = $this->sepa_valid_from && $this->sepa_valid_from != '0000-00-00' ? $this->sepa_valid_from : $this->created_at->toDateString();
		return strtotime($date);
	}


	/**
	 * Returns start time of item - Note: sepa_valid_from field has higher priority than created_at
	 *
	 * @return integer 		time in seconds after 1970
	 */
	public function get_end_time()
	{
		return $this->sepa_valid_to && $this->sepa_valid_to != '0000-00-00' ? strtotime($this->sepa_valid_to) : null;
	}


}


/**
 * Observer Class
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class SepaMandateObserver
{

	public function creating($mandate)
	{
		// build mandate reference from template
		$mandate->reference = $mandate->reference ? : $this->build_mandate_ref($mandate);

		// Set default values for empty fields - NOTE: prepare_input() functions fills data too
		$mandate->sepa_holder = $mandate->sepa_holder ? : $mandate->contract->firstname.' '.$mandate->contract->lastname;
		$mandate->signature_date = $mandate->signature_date ? : date('Y-m-d');
		$mandate->sepa_valid_from = $mandate->sepa_valid_from ? : date('Y-m-d');

		// set end date of old mandate to starting date of new mandate - Note: commented because multiple mandates with different cost centers are possible now
		// $mandate_old = $mandate->contract->get_valid_mandate();
		// if ($mandate_old)
		// {
		// 	$mandate_old->sepa_valid_to = date('Y-m-d', strtotime('-1 day', strtotime($mandate->sepa_valid_from)));
		// 	$mandate_old->save();
		// }
	}

	public function updating($mandate)
	{
		if (!$mandate->reference)
			$mandate->reference = $this->build_mandate_ref($mandate);

		if (!$mandate->signature_date || $mandate->signature_date == '0000-00-00')
			$mandate->signature_date = date('Y-m-d');
	}


	/**
	 * Replaces placeholders from in Global Config defined mandate reference template with values of mandate or the related contract
	 */
	private function build_mandate_ref($mandate)
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

		// foreach ($mandate->contract['attributes'] as $key => $value)
		// 	$template = str_replace('{'.$key.'}', $value, $template);

		// foreach ($mandate['attributes'] as $key => $value)
		// 	$template = str_replace('{'.$key.'}', $value, $template);

		return $template;
	}

}
