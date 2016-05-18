<?php

namespace Modules\BillingBase\Entities;
use Modules\ProvBase\Entities\Contract;
use DB;

class SepaMandate extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'sepamandate';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'signature_date' 	=> 'date',
			'sepa_iban' 		=> 'required|iban',
			'sepa_bic' 			=> 'bic',
			// 'sepa_institute' 	=> ,
			'sepa_valid_from' 	=> 'date',
			'sepa_valid_to'		=> 'dateornull'
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'SEPA Mandate';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->sepa_valid_from.' - '.$this->sepa_valid_to;
		// return $this->reference.' | '.$this->sepa_valid_from.' - '.$this->sepa_valid_to;
	}

	// Return a pre-formated index list
	public function index_list ()
	{
		return $this->orderBy('id')->get();
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
		$mandate->reference = $this->build_mandate_ref($mandate);

		$mandate->sepa_iban = strtoupper($mandate->sepa_iban);
		$mandate->sepa_bic  = strtoupper($mandate->sepa_bic);

		// Set default values for empty fields
		if (!$mandate->sepa_holder)
		{
			$contract = $mandate->contract;
			$mandate->sepa_holder = $contract->firstname.' '.$contract->lastname;
		}

		if (!$mandate->signature_date)
			$mandate->signature_date = date('Y-m-d');

		if (!$mandate->sepa_valid_from)
			$mandate->sepa_valid_from = date('Y-m-d', strtotime('next day'));


		// set end date of old mandate to starting date of new mandate
		$mandate_old = $mandate->contract->get_valid_mandate();

		if ($mandate_old)
		{
			$mandate_old->sepa_valid_to = date('Y-m-d', strtotime('-1 day', strtotime($mandate->sepa_valid_from)));
			$mandate_old->save();
		}

	}

	public function updating($mandate)
	{
		if (!$mandate->reference)
			$mandate->reference = $this->build_mandate_ref($mandate);

		if (!$mandate->signature_date || $mandate->signature_date == '0000-00-00')
			$mandate->signature_date = date('Y-m-d');

		$mandate->sepa_iban = strtoupper($mandate->sepa_iban);
		$mandate->sepa_bic  = strtoupper($mandate->sepa_bic);
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