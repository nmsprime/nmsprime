<?php

namespace Modules\BillingBase\Entities;

use Modules\BillingBase\Entities\Product;
use Carbon\Carbon;

class Item extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'item';

	// Add your validation rules here
	public static function rules($id = null)
	{
		$tariff_prods_o = Product::where('type', '=', 'internet')->orWhere('type', '=', 'tv')->orWhere('type', '=', 'voip')->get();
		if ($tariff_prods_o->all())
		{
			foreach ($tariff_prods_o as $p)
				$tariff_prods_a[] = $p->id;
			$tariff_ids = implode(',', $tariff_prods_a);
		}
		else
			$tariff_ids = '';

		$credit_prods_o = Product::where('type', '=', 'credit')->get();
		if ($credit_prods_o->all())
		{
			foreach ($credit_prods_o as $p)
				$credit_prods_a[] = $p->id;
			$credit_ids = implode(',', $credit_prods_a);
		}
		else
			$credit_ids = '';


		return array(
			// 'name' => 'required|unique:cmts,hostname,'.$id.',id,deleted_at,NULL'  	// unique: table, column, exception , (where clause)
			'valid_from'	=> 'dateornull',	//|in_future ??
			'valid_to'		=> 'dateornull',
			'credit_amount' => 'required_if:product_id,'.$credit_ids,
			'count'			=> 'null_if:product_id,'.$tariff_ids.','.$credit_ids,
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function view_headline()
	{
		return 'Item';
	}

	// link title in index view
	public function view_index_label()
	{
		$start = $end = '';
		if ($this->valid_from != '0000-00-00')
			$start = ' - '.$this->valid_from;
		if ($this->valid_to != '0000-00-00')
			$end = ' - '.$this->valid_to;
		return $this->product->name.$start.$end;
	}

	public function view_belongs_to ()
	{
		return $this->contract;
	}

	/**
	 * Relationships:
	 */

	public function product ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\Product', 'product_id');
	}

	public function contract ()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Contract');
	}


	/*
	 * Init Observers
	 */
	public static function boot()
	{
		Item::observe(new ItemObserver);
		parent::boot();
	}



	// Checks if item has valid dates in last month
	public function check_validity($start = '', $end = '')
	{
		return parent::check_validity('valid_from', 'valid_to');
	}


	/*
	 * Returns time in seconds after 1970 of start of item - valid_from field has higher priority than created_at
	 */
	public function get_start_time()
	{
		$date = $this->valid_from && $this->valid_from != '0000-00-00' ? $this->valid_from : $this->created_at->toDateString();
		return strtotime($date);

		// return $this->valid_from && $this->valid_from != '0000-00-00' ? Carbon::createFromFormat('Y-m-d', $this->valid_from) : $this->created_at;
		// $start = ($this->valid_from && $this->valid_from != $dates['null'] && strtotime($this->valid_from) > strtotime($this->created_at)) ? $this->valid_from : $this->created_at->toDateString();
	}


	/**
	 * Calculate Price for actual month of an item with valid dates
	 *
	 * @param 	array of billing dates (important is last run entry), costcenter (for billing_cycle)
	 * @return 	$price, $text (name and range of payment), $ratio
	 * @author 	Nino Ryschawy
	 */
	public function calculate_price_and_span($dates, $costcenter)
	{
		$price = 0;
		$ratio = 0;
		$text  = '';

		$billing_cycle = $this->billing_cycle ? $this->billing_cycle : $this->product->billing_cycle;
		$start = $this->get_start_time();
		$end = $this->valid_to == $dates['null'] ? null : strtotime($this->valid_to);
		// $end   = $this->valid_to == $dates['null'] ? null : Carbon::createFromFormat('Y-m-d', $this->valid_to);

		// contract ends before item ends - contract has higher priority
		if ($this->contract->expires)
			$end = !$end || strtotime($this->contract->contract_end) < $end ? strtotime($this->contract->contract_end) : $end;


		$overlapping = 0;
		// only 1 internet & voip tariff ! or if they overlap - old tariff has to be charged until new tariff begins
		if ($this->product->type == 'Internet')
		{
			// get start of valid tariff
			$valid_tariff = $this->contract->get_valid_tariff('Internet');

			if (!$valid_tariff)
				return null;

			// set end date of overlapping tariff
			if ($valid_tariff && $this->id != $valid_tariff->id)
			{
				$end = !$end || $end > $valid_tariff->get_start_time() ? $valid_tariff->get_start_time() : $end;
				$overlapping = 1;
			}
		}


		// only 1 internet & voip tariff ! or if they overlap - old tariff has to be charged until new tariff begins
		if ($this->product->type == 'Voip')
		{
			// get start of valid tariff
			$valid_tariff = $this->contract->get_valid_tariff('Voip');

			if (!$valid_tariff)
				return null;

			// set end date of overlapping tariff
			if ($valid_tariff && $this->id != $valid_tariff->id)
			{
				$end = !$end || $end > $valid_tariff->get_start_time() ? $valid_tariff->get_start_time() : $end;
				$overlapping = 1;
			}
		}


		// skip all items that have no valid dates in this month
		if ($start >= strtotime($dates['nextm_01']) || ($end && $end < strtotime($dates['thism_01'])))
			goto end;

		$started_lastm = (date('Y-m-01', $start) == $dates['lastm_01']) && ($start >= strtotime($dates['last_run']));


		switch($billing_cycle)
		{
			case 'Monthly':

				$text = 'Month '.$dates['this_m_bill'];

				$ratio = 1;

				// payment starts this month
				if (date('Y-m', $start) == $dates['this_m'])
					$ratio = 1 - (date('d', $start) - 1) / date('t');

				// payment starts last month after last_run
				if (date('Y-m-01', $start) == $dates['lastm_01'] && $start >= strtotime($dates['last_run']))
				{
					$ratio = 2 - (date('d', $start) - 1) / date('t', strtotime($dates['lastm_01']));
					$text  = 'Month '.$dates['last_m'].'+'.$dates['this_m_bill'];
				}

				// payment ends this month
				if ($end && $end < strtotime($dates['nextm_01']))
					$ratio += (date('d', $end) - $overlapping)/date('t') - 1;

// if ($this->contract->id == 500003 && $this->product->id == 4)
// 	dd($this->product->name, date('Y-m-d', $start), $end, date('Y-m-d', $end), $start, $ratio);


				$price = $ratio * $this->product->price;
				$text  = $this->product->name.' - '.$text;

				if ($this->product->type == 'Credit')
					$price = (-1) * $this->credit_amount;

				break;


			case 'Yearly':

				$billing_month = $costcenter->billing_month ? $costcenter->billing_month : 6;		// June as default
				if ($billing_month < 10)
					$billing_month = '0'.$billing_month;

				// calculate only for billing month
				if ($dates['m'] == $billing_month)
				{
					// started before this yr
					if (date('Y', $start) < $dates['Y'])
					{
						$ratio = 1;
						$text  = 'Year '.$dates['Y'];
					}

					// started this yr
					if (date('Y', $start) == $dates['Y'])
					{
						// $ratio = 1 - (date('m', $start)-1)/12;
						$ratio = 1 - date('z', $start) / (date('z', strtotime(date('Y-12-31'))) + 1);		// date('z') + 1 is actual day of year!
						$text  = $started_lastm ? $dates['last_m_bill'] : $dates['this_m_bill'];
						$text .= ' - '.date('12/Y');
					}
				}

				// started after last run in billing_month - only one payment!
				else if ($start >= strtotime(date("Y-$billing_month-".date('d', strtotime($dates['last_run'])) )) && (date('m', $start) == $dates['m'] || $started_lastm))
				{
					// pay to end of year
					$ratio = 1 - date('z', $start) / (date('z', strtotime(date('Y-12-31'))) + 1);
					$text  = $started_lastm ? $dates['last_m_bill'] : $dates['this_m_bill'];
					$text .= ' - '.date('12/Y');
				}

				// product validity ends this yr
				if ($end && date('Y', $end) == $dates['Y'])
				{
					// $ratio = $ratio ? $end_month/12 - 1 + $ratio : 0; // $end_month/12;
					$ratio = $ratio ? (date('z', $end) + 1)/(date('z', strtotime(date('Y-12-31'))) + 1) + $ratio - 1 : 0;
					$text  = substr($text, 0, strpos($text, '-') + 2).date('m/Y', $end);
				}

				$price = $this->product->price * $ratio;
				$text = $this->product->name.' '.$text;

				break;


			case 'Quarterly':
				$price = 0;
				$text  = '';

				// always in second of three months (1 -> 2,5,8,11 2->3,6,9,12 3->4,7,10,1)
				if (date('m', strtotime('+1 month', $start)) % 3 == $dates['m'] % 3)
				{
					$ratio = 1;
					$text  = date('m/Y', strtotime('-1 month')).' - '.date('m/Y', strtotime('+1 month'));

					// if started this or last month
					if ($start >= strtotime($dates['thism_01']) || $started_lastm)
					{
						$days = date('z', strtotime(date('Y-m-01', strtotime('+2 month')))) - date('z', $start) - 1;
						$total_days = date('t', strtotime('last month')) + date('t') + date('t', strtotime('next month'));
						$ratio = $days / $total_days;
					}
				}

				$end_m = date('m', $end);

				// consider end date
				if ($end_m == $dates['m'] || $end_m == date('m', strtotime('next month')))
				{
					// $price = $price * 2/3;
					$total_days = date('t', strtotime('last month')) + date('t') + date('t', strtotime('next month'));
					$ratio = (date('z', $end) - date('z', $start)) / $total_days;
					$text  = date('m/Y', strtotime('-1 month')).' - '.date('m/Y', $end);
				}
				// ends the next but one - endet übernächsten monat
				else if ($end_m == date('m', strtotime('+2 month')))
				{
					$ratio = 1 + (date('d', $end)/date('t', $end));
					$text  = date('m/Y', strtotime('-1 month')).' - '.date('m/Y', strtotime('+2 month'));
				}

				$price = $this->product->price * $ratio;
				$text = $this->product->name.' '.$text;

				break;


			case 'Once':
				$price = 0;
				$valid_to = $this->valid_to == $dates['null'] ? null : $this->valid_to;

				// if created or valid from this month or last month after last run
				if ($start >= strtotime($dates['thism_01']) || $started_lastm)
				{
					$price = $this->product->price;
					if ($this->product->type == 'Credit')
						$price = (-1) * $this->credit_amount;
				}

				// valid from - to
				if ($valid_to)
				{
					// split payment into pieces
					$tot_months = round((strtotime(date('Y-m', strtotime($valid_to))) - strtotime(date('Y-m', $start))) / $dates['m_in_sec']) + 1;
					if ($started_lastm)
						$tot_months -= 1;

					$price = $this->product->price / $tot_months;

					// $part = totm - (to - this)
					$part = round((($tot_months)*$dates['m_in_sec'] + strtotime($dates['thism_01']) - strtotime($valid_to))/$dates['m_in_sec']) + 1;
					$text = " | part $part/$tot_months";

					// items with valid_to in future, but contract expires
					if ($this->contract->expires)
					{
						$price = ($tot_months - $part + 1) * $price;
						$text = " | last ".($tot_months-$part+1)." part(s) of $tot_months";
					}

					if ($this->product->type == 'Credit')
						$price = (-1) * $this->credit_amount;
				}

				$text = $this->product->name.$text;

				if ($this->count)
				{
					$price *= $this->count;
					$text  = $this->count.'x '.$text;
				}

				break;

		}
end:

		$ratio = $ratio ? $ratio : 1;
		if (!$price)
			return null;

		return ['price' => $price, 'text' => $text, 'ratio' => $ratio];
	}



}



/**
 * Observer Class
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class ItemObserver
{
	public function creating($item)
	{
		switch ($item->product->type)
		{
			case 'Internet':
			case 'Voip':
			case 'TV':
				if (!$item->valid_from)
					$item->valid_from = date('Y-m-d');
				break;
		}
	}

	public function created($item)
	{
		if ($item->product->type == 'Internet' || $item->product->type == 'Voip')
		{
			// NOTE: keep this order!
			$item->contract->daily_conversion();
			$item->contract->push_to_modems();
		}
	}

	public function updated($item)
	{
		if ($item->product->type == 'Internet' || $item->product->type == 'Voip')
		{
			$item->contract->daily_conversion();
			$item->contract->push_to_modems();
		}
	}

	public function deleted($item)
	{
		if ($item->product->type == 'Internet' || $item->product->type == 'Voip')
		{
			$item->contract->daily_conversion();
			$item->contract->push_to_modems();
		}
	}


}