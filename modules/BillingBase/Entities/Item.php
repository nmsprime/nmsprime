<?php

namespace Modules\BillingBase\Entities;

use Modules\BillingBase\Entities\Product;

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
			// 'valid_from'  => 'required_if:product_id,'.$tariff_ids,
			'credit_amount' => 'required_if:product_id,'.$credit_ids,
			'count'			=> 'null_if:product_id,'.$tariff_ids.','.$credit_ids,
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'Item';
	}

	// link title in index view
	public function get_view_link_title()
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



	/**
	 * Calculate Price for actual month
	 *
	 * @param 	array of billing dates, costcenter (for billing_cycle)
	 * @return 	price, text (name and range of payment)
	 * @author 	Nino Ryschawy
	 */
	public function calculate_price_and_span($dates, $costcenter)
	{
		$price = 0;
		$ratio = 0;
		
		$billing_cycle  = $this->billing_cycle ? $this->billing_cycle : $this->product->billing_cycle;
		$start = ($this->valid_from && ($this->valid_from != $dates['null'])) ? $this->valid_from : $this->created_at;

		$end   = ($this->valid_to && ($this->valid_to != $dates['null'])) ? $this->valid_to : null;
		if ($this->contract->contract_end && $this->contract->contract_end != $dates['null'])
		{
			if (!$end || strtotime($this->contract->contract_end) < strtotime($end))
				$end = $this->contract->contract_end;
		}

		switch($billing_cycle)
		{
			case 'Monthly':
				
				$price = $this->product->price;
				$text  = 'Month '.$dates['this_m_bill'];

				// payment starts this month
				if (date('Y-m', strtotime($start)) == $dates['this_m'])
				{
					$ratio = 1 - date('d', strtotime($start)) / date('t');
					$price = round($price * $ratio, 2);
				}

				// payment starts last month after last_run
				if (date('Y-m-01', strtotime($start)) == $dates['lastm_01'] && strtotime($start) > strtotime($dates['last_run']))
				{
					$ratio = 1 - date('d', strtotime($start)) / date('t', strtotime($dates['lastm_01']));
					$price = round((1 + $ratio) * $price, 2);
					$text  = 'Month '.$dates['m'].'+'.$dates['this_m_bill'];
				}

				$text = $this->product->name.' '.$text;
				
				break;


			case 'Yearly':
				$price = 0;
				$text  = '';
				$billing_month = $costcenter->billing_month;
				$started_lastm = date('Y-m-01', strtotime($start)) == $dates['lastm_01'] && strtotime($start) > strtotime($dates['last_run']) ? true : false;

				// all 12 months from that month it is valid
				if (!$billing_month)
				{
					$max_ending = strtotime('+1 year');

					if (date('m', strtotime($start)) == $dates['m'] || $started_lastm);
					{
						$price = $this->product->price;
						$text  = $dates['this_m_bill'].' - '.date('m/Y', strtotime('now', strtotime('-1 month +1 year')));
						if ($started_lastm)
							$text = $dates['last_m_bill'].' - '.date('m/Y', strtotime('now', strtotime('-2 month +1 year')));
					}

					// consider valid_to date	
					if ($end && (strtotime($end) < $max_ending))
					{
						$price *= 1 + (date('m', $end) - $dates['m']) / 12;
						$text  = substr($text, 0, strpos($text, '-') + 1).date("$end_month/Y");
						break;
					}
				}

				$starting = date('m', strtotime($start));

				// started after billing_month - to end of year
				if (date("Y-$starting") >= date("Y-$billing_month") && ($starting == $dates['m'] || $started_lastm))
				{
					$price = $this->product->price * (1 - $starting/12);
					$text  = $dates['this_m_bill'];
					if ($started_lastm)
					{
						$text = $dates['last_m_bill'];
						$price = $this->product->price * (1 - ($starting-1)/12);
					}
					$text .= ' - '.date('12/Y');
					goto end;
				}


				// started before billing_month - calculate only for billing month
				if ($dates['m'] == $billing_month && $starting < $billing_month)
				{
					// started before this yr
					if (date('Y', strtotime($start)) < $dates['Y'])
					{
						$price = $this->product->price;
						$text  = 'Year '.$dates['Y'];
					}

					// started this yr
					if (date('Y', strtotime($start)) == $dates['Y'])
					{
						$price = $this->product->price * (1 - $starting/12);
						$text  = $dates['this_m_bill'].' - '.date('12/Y');
					}					
				}
end:
				// product validity ends this yr
				if ($end && date('Y', strtotime($end)) == $dates['Y'])
				{
					$end_month = date('m', strtotime($end));
					$price = $price * (1 - $end_month/12);
					$text  = substr($text, 0, strpos($text, '-') + 1).date("$end_month/Y");
				}

				$text = $this->product->name.' '.$text;

				break;


			case 'Quarterly':
				$price = 0;
				$text  = '';

				// 1 -> 2,5,8,11 2->3,6,9,12 3->4,7,10,1
				if (date('m', strtotime('+1 month', $start)) % 3 == $dates['m'] % 3)
				{
					$price = $this->product->price;
					$text  = date('m/Y', strtotime('-1 month')).' - '.date('m/Y', strtotime('+1 month'));
				}

				// consider valid_to date
				if (date('m', strtotime($end)) == $dates['m'])
				{
					$price = $price * 2/3;
					$text  = date('m/Y', strtotime('-1 month')).' - '.date('m/Y');
				}

				if (date('m', strtotime($end)) == date('m', strtotime('+2 month')))
				{
					$price = $price * 4/3;
					$text  = date('m/Y', strtotime('-1 month')).' - '.date('m/Y', strtotime('+2 month'));				
				}

				$text = $this->product->name.' '.$text;

				break;


			case 'Once':
				$price = 0;
				$valid_to = ($this->valid_to && $this->valid_to != $dates['null']) ? $this->valid_to : null;

				if (date('Y-m', strtotime($start)) == $dates['this_m'] || $started_lastm)		// $started_lastm is start after last run
					$price = $this->product->price;

				if (strtotime($start) <= strtotime($dates['today']) && $valid_to && strtotime($valid_to) >= strtotime($dates['today']))
				{
					// split payment into pieces
					$tot_months = round((strtotime(date('Y-m', strtotime($valid_to))) - strtotime(date('Y-m', strtotime($start)))) / $dates['m_in_sec']) + 1;
					if ($started_lastm)
						$tot_months -= 1;

					// $part = totm - (to - this)
					$part = round((($total_months)*$m_in_sec + strtotime($dates['thism_01']) - strtotime($valid_to))/$m_in_sec);
					$text = " | part $part/$total_months";

					// items with valid_to in future, but contract expires
					if (date('Y-m', strtotime($end)) == $dates['this_m'])
					{
						$price = ($total_months - $part + 1) * $price;
						$text = " | last ".$total_months-$part." parts of $total_months";
					}
				}

				$text = $this->product->name.$text;

				if ($this->count)
				{
					$price *= $this->count;
					$text = $this->count.'x '.$text;
				}

				break;

		}

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

	public function updating($item)
	{
	}

}