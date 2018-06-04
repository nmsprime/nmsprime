<?php

namespace Modules\BillingBase\Entities;

use ChannelLog, DB;

class Item extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'item';

	// Add your validation rules here
	public static function rules($id = null)
	{
		// $tariff_prods = Product::whereIn('type', ['internet', 'tv', 'voip'])->lists('id')->all();
		// $tariff_ids   = implode(',', $tariff_prods);

		// $credit_prods = Product::where('type', '=', 'credit')->lists('id')->all();
		// $credit_ids   = implode(',', $credit_prods);

		return array(
			// 'name' => 'required|unique:cmts,hostname,'.$id.',id,deleted_at,NULL'  	// unique: table, column, exception , (where clause)
			'product_id' 	=> 'required|numeric|Min:1',
			'valid_from'	=> 'date',	//|in_future ??
			'valid_to'		=> 'date',
			// 'credit_amount' => 'required_if:product_id,'.$credit_ids,
			// 'count'			=> 'null_if:product_id,'.$tariff_ids.','.$credit_ids,
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

	public static function view_icon()
	{
		return '<i class="fa fa-toggle-on"></i>';
	}

	// link title in index view
	public function view_index_label()
	{
		// TODO: simplify when it's secure that 0000-00-00 doesn't occure
		$start = $this->valid_from && $this->valid_from != '0000-00-00' ? ' - '.$this->valid_from : '';
		$end   = $this->valid_to && $this->valid_to != '0000-00-00' ? ' - '.$this->valid_to : '';

		if (!$this->product)
			return ['bsclass' => 'danger', 'header' => trans('messages.missing_product').$start.$end];

		// default value for fixed dates indicator – empty in most cases
		$start_fixed = '';
		$end_fixed = '';

		// for internet and voip items: mark not fixed dates (because they are possibly changed by daily conversion)
		if (in_array(\Str::lower($this->product->type), ['voip', 'internet'])) {
			if ($start) {
				$start_fixed = !boolval($this->valid_from_fixed) ? '(!)' : '';
			}
			if ($end) {
				$end_fixed = !boolval($this->valid_to_fixed) ? '(!)' : '';
			}
		}

		$count = $this->count > 1 ? "$this->count x " : '';
		$price = floatval($this->credit_amount) ? : $this->product->price;
		$price = ' | '.round($price, 2).'€';

		/* Evaluate Colours
		 	* green: it will be considered for next accounting cycle
		 	* blue:  new item - not yet considered for settlement run
			* yellow: item is outdated/expired and will not be charged this month
			* red: error error
		 */
		$billing_valid = $this->check_validity($this->product->billing_cycle);
		$bsclass = $billing_valid ? 'success' : ($this->get_start_time() < strtotime('midnight first day of this month') ? 'warning' : 'info');

		return ['index' => [$this->product->name, $start, $end],
		        'index_header' => ['Type', 'Name', 'Price'],
		        'bsclass' => $bsclass,
		        'header' => $count.$this->product->name.$start.$start_fixed.$end.$end_fixed.$price];
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
		return $this->belongsTo('Modules\BillingBase\Entities\CostCenter');
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
	public $charge = 0;


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
		return $this->product->billing_cycle;
		// return $this->billing_cycle ? $this->billing_cycle : $this->product->billing_cycle;
	}

	/**
	 * Returns the assigned Costcenter (CC) by following descendend priorities (1. item CC -> 2. product CC -> 3. contract CC)
	 *
	 * @return object 	Costcenter
	 */
	public function get_costcenter()
	{
		return $this->costcenter ? : ($this->product->costcenter ? : $this->contract->costcenter);
	}


	/**
	 * Check if item is of type Tariff or not
	 *
	 * @return 	Integer 	1 - yes is Tariff, 0 - no it's another type
	 */
	public function is_tariff()
	{
		return in_array($this->product->type, ['Internet', 'Voip', 'TV']) ? 1 : 0;
	}


	/**
	 * Calculate Charge for item in last month
	 *
	 * @param 	array 	dates 			of often used billing dates
	 * @param 	bool 	return_array 	return [charge, ratio, invoice_descrption] if true
	 *
	 * @return 	null if no costs incurred, true otherwise - NOTE: Amount to Charge is currently stored in Item Models temp variable ($charge)
	 * @author 	Nino Ryschawy
	 */
	public function calculate_price_and_span($dates, $return_array = false, $update = true)
	{
		$ratio = 0;
		$text  = '';			// dates of invoice text

		$billing_cycle  = strtolower($this->get_billing_cycle());

		// evaluate start & end dates with higher priority to contracts start & end
		$item_start 	= $this->get_start_time();
		$item_end   	= $this->get_end_time();
		$contract_start = $this->contract->get_start_time();
		$contract_end   = $this->contract->get_end_time();

		if ($billing_cycle == 'once')
		{
			$start = $item_start;
			$end = $item_end;
		}
		else
		{
			// Note: start will always be set - end date can be open (null)
			$start = $contract_start > $item_start ? $contract_start : $item_start;

			// Note: cases are sorted by likelihood
			if (!$contract_end && !$item_end)
				$end = null;

			else if ($contract_end && !$item_end)
				$end = $contract_end;

			else if (!$contract_end && $item_end)
				$end = $item_end;

			else if ($contract_end && $item_end)
				$end = $contract_end < $item_end ? $contract_end : $item_end;
		}

		// skip invalid items
		if (!$this->check_validity($billing_cycle, null, [$start, $end])) {
			ChannelLog::debug('billing', 'Item '.$this->product->name." ($this->id) is outdated", [$this->contract->id]);
			return null;
		}

		// Carbon::createFromTimestampUTC

		switch($billing_cycle)
		{
			case 'monthly':

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


			case 'yearly':
				// discard already payed items
				if ($this->payed_month && ($this->payed_month != ((int) $dates['lastm'])))
					break;

				$costcenter    = $this->get_costcenter();
				$billing_month = $costcenter->get_billing_month();		// June is default


				// calculate only for billing month
				if ($billing_month != $dates['lastm'])
				{
					// or tariff started after billing month - then only pay on first settlement run - break otherwise
					if (!((date('m', $start) >= $billing_month) && (date('Y-m', $start) == $dates['lastm_Y'])))
						break;
				}

				// started this yr
				if (date('Y', $start) == $dates['Y'])
				{
					$ratio = 1 - date('z', $start) / (365 + date('L'));		// date('z')+1 is day in year, 365 + 1 for leap year + 1
					$text  = date('Y-m-d', $start);
				}
				else
				{
					$ratio = 1;
					$text  = date('Y-01-01');
				}

				$text .= ' - ';

				// ended this yr
				if ($end && (date('Y', $end) == $dates['Y']))
				{
					$ratio += $ratio ? (date('z', $end) + 1)/(365 + date('L')) - 1 : 0;
					$text  .= date('Y-m-d', $end);
				}
				else
					$text .= date('Y-12-31');

				// set payed flag to avoid double payment in case of billing month is changed during year
				if ($ratio && $update)
				{
					$this->payed_month = $dates['m'] - 1;				// is set to 0 every new year
					$this->observer_enabled = false;
					$this->save();
				}

				break;


			case 'quarterly':

				// always after 3 months
				$billing_month = date('m', strtotime('+2 month', $start));

				if ($dates['m'] % 3 != $billing_month % 3)
					break;

				$period_start = strtotime('midnight first day of -2 month');

				// started in last 3 months
				if ($start > $period_start)
				{
					$days = date('z', strtotime('last day of this month')) - date('z', $start) + 1;
					$total_days = date('t') + date('t', strtotime('first day of last month')) + date('t', $start);
					$ratio = $days / $total_days;
					$text = date('Y-m-d', $start);
				}
				else
				{
					$ratio = 1;
					$text  = date('Y-m-01', $period_start);
				}

				// ended in last 3 months
				if ($end && ($end > $period_start) && ($end < strtotime(date('Y-m-01', strtotime('next month')))))
				{
					$days = date('z', strtotime('last day of this month')) - date('z', $end);
					$total_days = date('t') + date('t', strtotime('first day of last month')) + date('t', $start);
					$ratio -= $days / $total_days;
					$text .= date('Y-m-d', $end);
				}
				else
					$text .= date('Y-m-31');

				break;


			case 'once':

				if (date('Y-m', $start) == $dates['lastm_Y'])
					$ratio = 1;

				$valid_to = $this->valid_to && $this->valid_to != $dates['null'] ? strtotime(date('Y-m', strtotime($this->valid_to))) : null;		// only month is considered

				// if payment is split
				if ($valid_to)
				{
					if ($dates['lastm_Y'] > date('Y-m', $start) && $dates['lastm_Y'] <= date('Y-m', $valid_to))
						$ratio = 1;

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

		$this->count  = $this->count ? : 1;
		$this->charge = $this->product->price;
		if ($this->product->type == 'Credit')
			$this->charge = (-1) * ($this->credit_amount ? : $this->product->price);

		$this->charge *= $ratio * $this->count;

		$this->ratio  = $ratio ? : 1;
		$this->invoice_description = $this->product->name.' '.$text;
		$this->invoice_description .= $this->accounting_text ? ' - '.$this->accounting_text : '';

		if ($return_array === true) {
            return array('charge' => $this->charge, 'ratio' => $this->ratio, 'invoice_description' => $this->invoice_description);
        }
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


	/**
	 * Get Tariffs End of Term and last Day of possible Cancelation
	 * Ende Tariflaufzeit und letztes Kündigungsdatum
	 *
	 * @author Nino Ryschawy
	 *
	 * @return Array 	[End of Term, Last possible Cancelation Day]
	 */
	public function get_next_cancel_date()
	{
		$default_pon = Product::$pon; 				// Default period of notice

		// Tariff with maturity (m) (Laufzeit) but open end
		if ($this->product->maturity)
		{
			$end = \Carbon\Carbon::createFromFormat('Y-m-d', $this->valid_from);
			$end->subDay();

			// add maturity period until the tarif end time is in future
			do {
				$end = self::_add_period($end, $this->product->maturity);
			} while ($end->toDateString() < date('Y-m-01', strtotime('first day of last month')));

			// get last day of period of notice (pon)
			$cancel_day = self::_add_period(clone $end, $this->product->period_of_notice ? : $default_pon, 'sub');

			// period of notice expired - extend runtime - Kündigungsfrist abgelaufen
			if ($cancel_day->isPast()) {
				$end = self::_add_period($end, $this->product->maturity);
				$cancel_day = self::_add_period($cancel_day, $this->product->maturity);
			}
		}
		else
		{
			$end = \Carbon\Carbon::create();
			$end = self::_add_period($end, $this->product->period_of_notice ? : $default_pon);
			$end->lastOfMonth();

			$cancel_day = self::_add_period(clone $end, $this->product->period_of_notice ? : $default_pon, 'sub');

			// period of notice expired - extend runtime
			if ($cancel_day->isPast()) {
				$end->addMonthNoOverflow();
				$cancel_day->addMonthNoOverflow();
			}
		}

		return array(
			'end_of_term' => $end->toDateString(),
			'cancelation_day' => $cancel_day->toDateString(),
			);
	}

	/**
	 * Add a time period to a time object
	 *
	 * @author Nino Ryschawy
	 *
	 * @param Object 	current datetime
	 * @param String 	e.g. 14D|3M|1Y (14 days|3 month|1 year)
	 * @param String 	subtract or add time period
	 */
	private static function _add_period(\Carbon\Carbon $dt, $period, $method = 'add')
	{
		// split nr from timespan
		$nr = preg_replace( '/[^0-9]/', '', $period);
		$span = str_replace($nr, '', $period);

		$d = $dt->__get('day');
		$m = $dt->__get('month');

		switch ($span)
		{
			case 'D':
				$dt->{$method.'Day'}($nr); break;

			case 'M':
				// handle last day of february
				$is_last = ($m == 2) && (($d == 28 && !$dt->isLeapYear()) || ($d == 29 && !$dt->isLeapYear()));

				$dt->{$method.'MonthNoOverflow'}($nr);

				if ($is_last)
					$dt->lastOfMonth();

				break;

			case 'Y':
				// handle last day of february
				if ($d == 29 && $m == 2 && ($nr % 4))
					$dt->subDay();

				$dt->{$method.'Year'}($nr);

				break;
		}

		return $dt;
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

		// \Log::debug('creating item');

		// set end date of old tariff to starting date of new tariff
		if (in_array($item->product->type, array('Internet', 'Voip', 'TV')))
		{
			if ($tariff)
			{
				$tariff->valid_to = date('Y-m-d', strtotime('-1 day', strtotime($item->valid_from)));
				$tariff->valid_to_fixed = $item->valid_from_fixed || $tariff->valid_to_fixed ? true : false;
				// do not call observer and daily conversion multiple times
				$tariff->observer_enabled = false;
				$tariff->save(); 								// calls updating & updated observer methods
			}
		}

		// $item->contract->update_product_related_data([$item, $tariff]);

		// set end date for products with fixed number of cycles
		$this->handle_fixed_cycles($item);

	}

	public function created($item)
	{
		// \Log::debug('created item', [$item->id]);

		// this is ab(used) here for easily setting the correct values
		$item->contract->daily_conversion();
	}


	public function updating($item)
	{
		if(!$item->observer_enabled)
			return;

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
				// do not consider updated items
				($tariff->id != $item->id)
			) {
				\Log::debug('update old tariff', [$item->id]);
				$tariff->valid_to = date('Y-m-d', strtotime('-1 day', strtotime($item->valid_from)));
				$tariff->valid_to_fixed = $item->valid_from_fixed || $tariff->valid_to_fixed ? true : false;
				// Maybe implement this as DB-Update-Statement to not call observer and daily conversion multiple times ??
				$tariff->observer_enabled = false;
				$tariff->save();
			}

			// check if we have to update product related data (qos, voip tariff, etc.) in contract
			// this has to be done for both objects - why? - is done for both in daily conversion after
			// $item->contract->update_product_related_data([$item, $tariff]);
		}

		// \Log::debug('updating item', [$item->id]);

		// set end date for products with fixed number of cycles
		$this->handle_fixed_cycles($item);

	}

	public function updated($item)
	{
		if(!$item->observer_enabled)
			return;

		// \Log::debug('updated item', [$item->id]);

		// this is ab(used) here for easily setting the correct values
		$item->contract->daily_conversion();
	}


	public function deleted($item)
	{
		// \Log::debug('deleted item', [$item->id]);

		// this is ab(used) here for easily setting the correct values
		$item->contract->daily_conversion();
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
