<?php

namespace Modules\BillingBase\Entities;

use DB;

class Item extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'item';

	// Add your validation rules here
	public static function rules($id = null)
	{
		$tariff_prods = Product::whereIn('type', ['internet', 'tv', 'voip'])->lists('id')->all();
		$tariff_ids   = implode(',', $tariff_prods);
		
		$credit_prods = Product::where('type', '=', 'credit')->lists('id')->all();
		$credit_ids   = implode(',', $credit_prods);

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
		$bsclass = '';
		// TODO: simplify when it's secure that 0000-00-00 doesn't occure
		$start = $this->valid_from && $this->valid_from != '0000-00-00' ? ' - '.$this->valid_from : '';
		$end   = $this->valid_to && $this->valid_to != '0000-00-00' ? ' - '.$this->valid_to : '';

		$start_fixed = !boolval($this->valid_from_fixed) ? '(!)' : '';
		$end_fixed = !boolval($this->valid_to_fixed) ? '(!)' : '';

		$billing_valid = $this->check_validity();

		// green colour means it will be considered for next accounting cycle, blue is a new item
		$bsclass = $billing_valid ? 'success' : 'info';

		// red means item is outdated
		if (($this->get_start_time() < strtotime(date('Y-m-01'))) && !$billing_valid)
			$bsclass = 'danger';

		$name = isset($this->product) ? $this->product->name : $this->accounting_text;

		return ['index' => [$name, $start, $end],
		        'index_header' => ['Type', 'Name', 'Price'],
		        'bsclass' => $bsclass,
		        'header' => $name.$start.$start_fixed.$end];

		// return $this->product->name.$start.$end;
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
		return $this->belongsTo('Modules\BillingBase\Entities\Product');
	}

	public function contract ()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Contract');
	}

	public function costcenter ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\Costcenter');
	}


	/*
	 * Init Observers
	 */
	public static function boot()
	{
		Item::observe(new ItemObserver);
		parent::boot();
	}



	/*
	 * Billing Stuff - Temporary Variables used during billing cycle
	 */


	/**
	 * The calculated charge for the customer that has purchased this item (last month is considered)
	 *
	 * @var float
	 */ 
	public $charge;


	/**
	 * The calculated ratio of the items product price (for the last month)
	 *
	 * @var float
	 */ 
	public $ratio;


	/**
	 * The product name and date range the customer is charged for this item
	 *
	 * @var string
	 */ 
	public $invoice_description;




	/**
	 * Returns start time of item - Note: valid_from field has higher priority than created_at
	 *
	 * @return integer 		time in seconds after 1970
	 */
	public function get_start_time()
	{
		$date = $this->valid_from && $this->valid_from != '0000-00-00' ? $this->valid_from : $this->created_at->toDateString();
		return strtotime($date);
	}


	/**
	 * Returns start time of item - Note: valid_from field has higher priority than created_at
	 *
	 * @return integer 		time in seconds after 1970
	 */
	public function get_end_time()
	{
		return $this->valid_to && $this->valid_to != '0000-00-00' ? strtotime($this->valid_to) : null;
	}


	/**
	 * Returns billing cycle
	 *
	 * @return String/Enum 	('Monthly', 'Yearly', 'Quarterly', 'Once')
	 */
	public function get_billing_cycle()
	{
		return $this->billing_cycle ? $this->billing_cycle : $this->product->billing_cycle;
	}

	/**
	 * Returns the assigned Costcenter (CC) by following descendend priorities (1. item CC -> 2. product CC -> 3. contract CC)
	 *
	 * @return object 	Costcenter
	 */
	public function get_costcenter()
	{
		return $this->costcenter ? $this->costcenter : ($this->product->costcenter ? $this->product->costcenter : $this->contract->costcenter);
	}


	/**
	 * Calculate Price for actual month of an item with valid dates - writes it to temporary billing variables of this model
	 *
	 * @param 	array  $dates 	of often used billing dates
	 * @return 	null if no costs incurred, 1 otherwise
	 * @author 	Nino Ryschawy
	 */
	public function calculate_price_and_span($dates)
	{
		$ratio = 0;
		$text  = '';			// only dates
		
		$billing_cycle = $this->get_billing_cycle();
		$start = $this->get_start_time();
		$end   = $this->get_end_time();

		// contract ends before item ends - contract has higher priority
		if ($this->contract->expires)
			$end = !$end || strtotime($this->contract->contract_end) < $end ? strtotime($this->contract->contract_end) : $end;


		switch($billing_cycle)
		{
			case 'Monthly':

				// started last month
				if (date('Y-m', $start) == $dates['lastm_Y'])
				{
					$ratio = 1 - (date('d', $start) - 1) / date('t', $start);
					$text  = date('Y-m-d', $start);
				}
				else
				{
					$ratio = 1;
					$text = $dates['lastm_01'];
				}

				$text .= ' - ';

				// ended last month
				if ($end && $end < strtotime($dates['thism_01']))
				{
					$ratio += date('d', $end)/date('t', $end) - 1;
					$text  .= date('Y-m-d', $end);
				}
				else
					$text  .= date('Y-m-d', strtotime('last day of last month'));

				break;

// if ($this->contract->id == 500003 && $this->product->type == 'Internet' && strpos($this->product->name, 'Flat 2 M') !== false)
// 	dd($this->product->name, date('t', $start), $end, date('Y-m-d', $end), $ratio, $billing_cycle, $text);


			case 'Yearly':

				if ($this->payed_month && $this->payed_month != $dates['m'] - 1)
					break;

				// calculate only for billing month
				$costcenter    = $this->get_costcenter();
				$billing_month = $costcenter->get_billing_month();		// June is default

				if ($dates['m'] - 1 != $billing_month)
					break;

				// started last yr
				if (date('Y', $start) == ($dates['Y'] - 1))
				{
					$ratio = 1 - date('z', $start) / (366 + date('L'));		// date('z')+1 is day in year, 365 + 1 for leap year + 1 
					$text  = date('Y-m-d', $start);
				}
				else
				{
					$ratio = 1;
					$text  = date('Y-01-01', strtotime('last year'));
				}

				$text .= ' - ';

				// ended last yr
				if ($end && (date('Y', $end) == ($dates['Y'] - 1)))
				{
					$ratio += $ratio ? (date('z', $end) + 1)/(366 + date('L')) - 1 : 0;
					$text  .= date('Y-m-d', $end);
				}
				else
					$text .= date('Y-12-31', strtotime('last year'));

				// set payed flag to avoid double payment in case of billing month is changed during year
				if ($ratio)
				{
					$this->payed_month = $dates['m'] - 1;				// is set to 0 every new year
					$this->save();
				}

				break;


			case 'Quarterly':

				// always after 3 months
				$billing_month = date('m', strtotime('+2 month', $start));

				if ($dates['m'] % 3 != $billing_month % 3)
					break;

				$period_start = date('Y-m-01', strtotime('-2 month'));

				// started in last 3 months
				if ($start > strtotime($period_start))
				{
					$days = date('z', strtotime('last day of this month')) - date('z', $start) + 1;
					$total_days = date('t') + date('t', strtotime('first day of last month')) + date('t', $start);
					$ratio = $days / $total_days;
					$text = date('Y-m-d', $start);
				}
				else
				{
					$ratio = 1;
					$text  = $period_start;
				}

				// ended in last 3 months
				if ($end && ($end > strtotime($period_start)) && ($end < strtotime(date('Y-m-01', strtotime('next month')))))
				{
					$days = date('z', strtotime('last day of this month')) - date('z', $end);
					$total_days = date('t') + date('t', strtotime('first day of last month')) + date('t', $start);
					$ratio -= $days / $total_days;
					$text .= date('Y-m-d', $end);
				}
				else
					$text .= date('Y-m-31');


				// always in second of three months (1 -> 2,5,8,11 2->3,6,9,12 3->4,7,10,1)
				// if (date('m', strtotime('+1 month', $start)) % 3 != $dates['m'] % 3)
				// 	break;

				// $ratio = 1;
				// $end_m = date('m', $end);
				// $n = strtotime('last day of next month');
				// $l = strtotime('first day of last month');

				// // started last month
				// if (date('Y-m', $start) == $dates['lastm_Y'])
				// {
				// 	$days  = date('z', $n) - date('z', $start) + 1;
				// 	$total_days = date('t', $l) + date('t') + date('t', $n);
				// 	$ratio = $days / $total_days;
				// 	$text  = date('Y-m-d', $start);
				// }
				// // started before
				// else
				// 	$text = date('Y-m-01', $l);

				// $text .= ' ';

				// // consider end date - if is during this 3 months
				// if ($end_m == $dates['m'] || $end_m == date('m', $n) || $end_m == date('m', $l))
				// {
				// 	$days  = date('z', $l) - date('z', $end) - 1;
				// 	$total_days = date('t', $l) + date('t') + date('t', $n);
				// 	$ratio -= $days / $total_days;
				// 	$text  .= date('Y-m-d', $end);
				// }
				// else
				// 	$text .= date('Y-m-d', $n);

				break;


			case 'Once':

				if (date('Y-m', $start) == $dates['lastm_Y'])
					$ratio = 1;

				$valid_to = $this->valid_to && $this->valid_to != $dates['null'] ? strtotime(date('Y-m', strtotime($this->valid_to))) : null;		// only month is considered

				// if payment is split
				if ($valid_to)
				{
					$tot_months = round(($valid_to - strtotime(date('Y-m', $start))) / $dates['m_in_sec'] + 1, 0);
					$ratio /= $tot_months;

					// $part = totm - (to - this)
					$part = round((($tot_months)*$dates['m_in_sec'] + strtotime($dates['lastm_01']) - $valid_to)/$dates['m_in_sec']);
					$text = " | part $part/$tot_months";

					// items with valid_to in future, but contract expires
					if ($this->contract->expires)
					{
						$ratio *= $tot_months - $part + 1;
						$text  = " | last ".($tot_months-$part+1)." part(s) of $tot_months";
					}
				}

				break;

		}

		if (!$ratio)
			return null;

		$this->count = $this->count ? $this->count : 1;

		$this->charge = $this->product->type == 'Credit' ?  (-1) * $this->credit_amount : $this->product->price * $ratio * $this->count;
		$this->ratio  = $ratio ? $ratio : 1;
		$this->invoice_description = $this->product->name.' '.$text;

		return true;
	}


	/**
	 * Resets the payed flag of all items - flag is necessary because the billing timestamp can be changed during year
	 */
	public function yearly_conversion()
	{
		DB::table($this->table)->update(['payed_month' => 0]);
		\Log::info('Billing: Payed month flag of all items resettet for new year');
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
		// this doesnt work in prepare_input() !!
		$item->valid_to = $item->valid_to ? : null;
		$tariff = $item->contract->get_valid_tariff($item->product->type);

		// set end date of old tariff to starting date of new tariff
		if (in_array($item->product->type, array('Internet', 'Voip', 'TV')))
		{
			if ($tariff)
			{
				$tariff->valid_to = date('Y-m-d', strtotime('-1 day', strtotime($item->valid_from)));
				$tariff->save();
			}
		}

		$item->contract->update_product_related_data([$item, $tariff]);

		// set end date for products with fixed number of cycles
		$this->handle_fixed_cycles($item);

	}


	public function updating($item)
	{
		// this doesnt work in prepare_input() !!
		$item->valid_to = $item->valid_to ? : null;

		// set end date of old tariff to starting date of new tariff (if it's not the same)
		if (in_array($item->product->type, array('Internet', 'Voip', 'TV')))
		{
			$tariff = $item->contract->get_valid_tariff($item->product->type);

			if (
				$tariff
				&&
				// prevent from writing smaller valid_to than valid_from which
				// before adding this was caused by daily_conversion
				($tariff->valid_from < $item->valid_from)
				&&
				// obsoleted by the above – but left here to keep the original condition
				($tariff->id != $item->id)
			) {
				$tariff->valid_to = date('Y-m-d', strtotime('-1 day', strtotime($item->valid_from)));
				$tariff->save();
			}
			else {
				$tariff = null;
			}

			// check if we have to update product related data (qos, voip tariff, etc.) in contract
			// this has to be done for both objects
			$item->contract->update_product_related_data([$item, $tariff]);
		}


		// set end date for products with fixed number of cycles
		$this->handle_fixed_cycles($item);

	}


	/**
	 * Auto fills valid_from and valid_to fields for items of products with fixed cycle count
	 */
	private function handle_fixed_cycles($item)
	{
		if (!$item->product->cycle_count)
			return;

		$cnt = $item->product->cycle_count;
		if ($item->product->billing_cycle == 'Quarterly') $cnt *= 3;
		if ($item->product->billing_cycle == 'Yearly') $cnt *= 12; 

		if(!$item->valid_from || $item->valid_from == '0000-00-00')
			$item->valid_from = date('Y-m-d');

		$item->valid_to = date('Y-m-d', strtotime('last day of this month', strtotime("+$cnt month", strtotime($item->valid_from))));
	}

}
