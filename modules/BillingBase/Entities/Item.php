<?php

namespace Modules\BillingBase\Entities;

use DB;
use ChannelLog;

class Item extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'item';

    /**
     * Call daily conversion in observer - disabled in Contract::daily_conversion to avoid endless loops
     *
     * @var bool
     */
    public $observer_dailyconversion = true;

    // Add your validation rules here
    public static function rules($id = null)
    {
        // see ItemController@prepare_rules

        return [
            'product_id' 	=> 'required|numeric|Min:1',
            'valid_from'	=> 'date',	//|in_future ??
            'valid_to'		=> 'date',
            'credit_amount' => 'nullable|numeric',
        ];
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
        $count = $this->count > 1 ? "$this->count x " : '';
        $dates = $this->dateLabels();
        $price = $this->getItemPrice();

        $ret = ['table' => $this->table,
                'index_header' => [
                    'contract.number',
                    'contract.firstname',
                    'contract.lastname',
                    'contract.city',
                    'contract.district',
                    'contract.contract_start',
                    'contract.contract_end',
                    'product.name',
                    $this->table.'.valid_from',
                    $this->table.'.valid_to',
                    'product.price',
                ],
                'eager_loading' => ['product', 'contract'],
            ];

        // if enabled add item.valid_from and item.valid_to to index page
        if ($this->fluidDates()) {
            array_push($ret['index_header'], $this->table.'.valid_from_fixed', $this->table.'.valid_to_fixed');
        }

        if ($this->product) {
            $ret['header'] = $count.
                $this->product->name.
                $dates['start'].
                $dates['startFixed'].
                $dates['end'].
                $dates['endFixed'].
                $price;
            $ret['bsclass'] = $this->get_bsclass();
        } else {
            $ret['bsclass'] = 'danger';
            $ret['header'] = trans('messages.missing_product').$dates['start'].$dates['end'];
        }

        return $ret;
    }

    /**
     * Get label bootstrap class for colorization
     *
     * green: valid
     * blue:  starts in future
     * grey:  outdated/expired
     */
    public function get_bsclass()
    {
        // Dont check when index table header is determined!
        if (! $this->id) {
            return '';
        }

        if ($this->product->billing_cycle == 'Once' && ! $this->get_end_time() && $this->get_start_time() < strtotime('midnight first day of this month')) {
            return 'active';
        }

        if ($this->check_validity('now')) {
            return 'success';
        }

        if ($this->get_start_time() < strtotime('midnight first day of this month')) {
            return 'active';
        }

        return 'info';
    }

    public function dateLabels()
    {
        // TODO: simplify when it's secure that 0000-00-00 doesn't occure
        $start = $this->valid_from && $this->valid_from != '0000-00-00' ? ' - '.$this->valid_from : '';
        $end = $this->valid_to && $this->valid_to != '0000-00-00' ? ' - '.$this->valid_to : '';

        // default value for fixed dates indicator – empty in most cases
        $startFixed = '';
        $endFixed = '';

        // for internet and voip items: mark not fixed dates (because they are possibly changed by daily conversion)
        if ($this->product && in_array(\Str::lower($this->product->type), ['voip', 'internet'])) {
            if ($start) {
                $startFixed = ! boolval($this->valid_from_fixed) ? ' (!)' : '';
            }
            if ($end) {
                $endFixed = ! boolval($this->valid_to_fixed) ? ' (!)' : '';
            }
        }

        return compact('end', 'start', 'endFixed', 'startFixed');
    }

    public function getItemPrice()
    {
        if ($this->product) {
            $price = floatval($this->credit_amount) ?: $this->product->price;
            $price = ' | '.round($price, 4).\Modules\BillingBase\Providers\Currency::get();

            return $price;
        }
    }

    /**
     * Check if item.valid_from/valid_to is enabled.
     *
     * @author Roy Schneider
     * @param void
     * @return bool
     */
    protected function fluidDates()
    {
        return BillingBase::first()->fluid_valid_dates;
    }

    public function view_belongs_to()
    {
        return $this->contract;
    }

    /**
     * Relationships:
     */
    public function product()
    {
        return $this->belongsTo('Modules\BillingBase\Entities\Product');
    }

    public function contract()
    {
        return $this->belongsTo('Modules\ProvBase\Entities\Contract');
    }

    public function costcenter()
    {
        return $this->belongsTo('Modules\BillingBase\Entities\CostCenter');
    }

    /*
     * Init Observers
     */
    public static function boot()
    {
        self::observe(new ItemObserver);
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
     * @return int 		time in seconds after 1970
     */
    public function get_start_time()
    {
        $date = $this->valid_from != '0000-00-00' ? $this->valid_from : $this->created_at->toDateString();

        return strtotime($date);
    }

    /**
     * Returns start time of item - Note: valid_from field has higher priority than created_at
     *
     * @return int 		time in seconds after 1970
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
        return $this->costcenter ?: ($this->product->costcenter ?: $this->contract->costcenter);
    }

    /**
     * Check if item is of type Tariff or not
     *
     * @return 	int 	1 - yes is Tariff, 0 - no it's another type
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
    public function calculate_price_and_span($return_array = false, $update = true)
    {
        $ratio = 0;
        $text = '';			// dates of invoice text
        $dates = \Modules\BillingBase\Providers\SettlementRunData::getDate();

        $billing_cycle = strtolower($this->get_billing_cycle());

        // evaluate start & end dates with higher priority to contracts start & end
        $item_start = $this->get_start_time();
        $item_end = $this->get_end_time();
        $contract_start = $this->contract->get_start_time();
        $contract_end = $this->contract->get_end_time();

        // Note: start will always be set - end date can be open (null)
        $start = $contract_start > $item_start ? $contract_start : $item_start;
        $end = null;    // if (! $contract_end && ! $item_end) {

        // Note: cases are sorted by likelihood
        if ($contract_end && ! $item_end) {
            $end = $contract_end;
        } elseif (! $contract_end && $item_end) {
            $end = $item_end;
        } elseif ($contract_end && $item_end) {
            $end = $contract_end < $item_end ? $contract_end : $item_end;
        }

        // skip invalid items
        if (! $this->check_validity($billing_cycle, null, [$start, $end])) {
            ChannelLog::debug('billing', 'Item '.$this->product->name." ($this->id) is outdated", [$this->contract->id]);

            return;
        }

        // Carbon::createFromTimestampUTC

        switch ($billing_cycle) {
            case 'monthly':

                if (! $this->product->proportional) {
                    $start = strtotime('first day of last month');
                    $end = strtotime('last day of last month');
                }

                // started last month
                if (date('Y-m', $start) == $dates['lastm_Y']) {
                    $ratio = 1 - (date('d', $start) - 1) / date('t', $start);
                    $text = Invoice::langDateFormat($start);
                } else {
                    $ratio = 1;
                    $text = Invoice::langDateFormat($dates['lastm_01']);
                }

                $text .= ' - ';

                // ended last month
                if ($end && $end < strtotime($dates['thism_01'])) {
                    $ratio += date('d', $end) / date('t', $end) - 1;
                    $text .= Invoice::langDateFormat($end);
                } else {
                    $text .= Invoice::langDateFormat(strtotime('last day of last month'));
                }

                break;

            case 'yearly':
                // discard already payed items
                if ($this->payed_month && ($this->payed_month != ((int) $dates['lastm']))) {
                    break;
                }

                $costcenter = $this->get_costcenter();
                $billing_month = $costcenter->get_billing_month();      // June is default

                // calculate only for billing month
                if ($billing_month != $dates['lastm']) {
                    // or tariff started after billing month - or contract ended last month (before billing month)
                    // then pay on next settlement run - break otherwise
                    if (! (date('Y-m', $start) >= date("Y-$billing_month") || date('Y-m', $contract_end) == $dates['lastm_Y'])) {
                        break;
                    }
                }

                // in case billing month is december we have to consider the last year as current month is january of this year
                $year = (int) $billing_month == 12 ? date('Y') - 1 : date('Y');

                if (! $this->product->proportional) {
                    $start = strtotime("$year-01-01");
                    $end = strtotime("$year-12-31");
                }

                // started this yr
                if (date('Y', $start) == $year) {
                    $ratio = 1 - date('z', $start) / (365 + date('L', strtotime("$year-01-01")));     // date('z')+1 is day in year, 365 + 1 for leap year + 1
                    $text = Invoice::langDateFormat($start);
                } else {
                    $ratio = 1;
                    $text = Invoice::langDateFormat("$year-01-01");
                }

                $text .= ' - ';

                // ended this yr
                if ($end && (date('Y', $end) == $year)) {
                    $ratio += $ratio ? (date('z', $end) + 1) / (365 + date('L', strtotime("$year-01-01"))) - 1 : 0;
                    $text .= Invoice::langDateFormat($end);
                } else {
                    $text .= Invoice::langDateFormat("$year-12-31");
                }

                // set payed flag to avoid double payment in case of billing month is changed during year
                if ($ratio && $update) {
                    $this->payed_month = $dates['lastm'];				// is set to 0 every new year
                    $this->observer_enabled = false;
                    $this->save();
                }

                break;

            case 'quarterly':

                $billing_months = ['02', '05', '08', '11'];

                if (! in_array($dates['lastm'], $billing_months)) {
                    break;
                }

                $period_start = strtotime('first day of -2 month');

                if (! $this->product->proportional) {
                    $start = strtotime('first day of -2 month');
                    $end = strtotime('last day of this month');
                }

                // started in last 3 months
                if ($start > $period_start) {
                    $days = date('z', strtotime('last day of this month')) - date('z', $start) + 1;
                    $total_days = date('t') + date('t', strtotime('first day of last month')) + date('t', $start);
                    $ratio = $days / $total_days;
                    $text = Invoice::langDateFormat($start);
                } else {
                    $ratio = 1;
                    $text = Invoice::langDateFormat(date('Y-m-01', $period_start));
                }

                // ended in last 3 months
                if ($end && ($end > $period_start) && ($end < strtotime(date('Y-m-01', strtotime('next month'))))) {
                    $days = date('z', strtotime('last day of this month')) - date('z', $end);
                    $total_days = date('t') + date('t', strtotime('first day of last month')) + date('t', $start);
                    $ratio -= $days / $total_days;
                    $text .= Invoice::langDateFormat($end);
                } else {
                    $text .= Invoice::langDateFormat(date('Y-m-d', strtotime('last day of this month')));
                }

                break;

            case 'once':

                if (date('Y-m', $start) == $dates['lastm_Y']) {
                    $ratio = 1;
                }

                $valid_to = $this->valid_to && $this->valid_to != $dates['null'] ? strtotime(date('Y-m', strtotime($this->valid_to))) : null;		// only month is considered

                if ($this->product->proportional) {
                    // started last month
                    if (date('Y-m-d', $start) != date('Y-m-01', strtotime('first day of last month'))) {
                        $ratio = 1 - (date('d', $start) - 1) / date('t', $start);
                        $text = Invoice::langDateFormat($start);
                    }

                    // ended last month - same as in monthly case
                    if ($end && $end < strtotime('first day of this month')) {
                        $ratio += date('d', $end) / date('t', $end) - 1;
                        $text = $text ?: Invoice::langDateFormat(strtotime('first day of last month'));
                        $text .= ' - '.Invoice::langDateFormat($end);
                    } elseif ($text) {
                        $text .= ' - '.Invoice::langDateFormat(strtotime('last day of last month'));
                    }
                } elseif ($valid_to) {
                    if (date('Y-m', $start) == $dates['lastm_Y'] && $end < strtotime('first day of this month')) {
                        $ratio = 1;

                        break;
                    }

                    // Payment is split over several months
                    if ($dates['lastm_Y'] > date('Y-m', $start) && $dates['lastm_Y'] <= date('Y-m', $valid_to)) {
                        $ratio = 1;
                    }

                    $tot_months = round(($valid_to - strtotime(date('Y-m', $start))) / $dates['m_in_sec'] + 1, 0);
                    $ratio /= $tot_months;

                    // $part = totm - (to - this)
                    $part = round((($tot_months) * $dates['m_in_sec'] + strtotime($dates['lastm_01']) - $valid_to) / $dates['m_in_sec']);
                    $text = ' | '.trans_choice('messages.parts', 1)." $part/$tot_months";

                    // items with valid_to in future, but contract expires
                    if ($this->contract->expires) {
                        $ratio *= $tot_months - $part + 1;
                        $total = $tot_months - $part + 1;
                        $text = ' | '.trans_choice('messages.last', $total)." $total ".trans_choice('messages.parts', $total).' '.trans('messages.of')." $tot_months";
                    }
                }

                break;
        }

        if (! $ratio) {
            return;
        }

        $this->count = $this->count ?: 1;
        $this->charge = $this->product->price;
        if ($this->product->type == 'Credit') {
            $this->charge = (-1) * (floatval($this->credit_amount) ?: $this->product->price);
        }

        $this->charge *= $ratio * $this->count;

        $this->ratio = $ratio ?: 1;
        $this->invoice_description = $this->accounting_text ?: $this->product->name;
        $this->invoice_description .= " $text";

        if ($return_array === true) {
            return ['charge' => $this->charge, 'ratio' => $this->ratio, 'invoice_description' => $this->invoice_description];
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
     * @return array 	[End of Term, Last possible Cancelation Day]
     *         null     on error
     */
    public function getNextCancelationDate($date = '')
    {
        if (! $this->product) {
            return;
        }

        $ret = [
            'cancelation_day' => '',
            'canceled_to' => '',
            'end_of_term' => '',
            'maturity' => '',
        ];

        // Item was already canceled
        if (! $this->isDirty('valid_to') && $this->valid_to && ($tmp = $this->get_end_time())) {
            $ret['canceled_to'] = date('Y-m-d', $tmp);

            return $ret;
        }

        if (! $date) {
            $date = date('Y-m-d');
        }

        // determine tariff/item's end of term (minimum maturity) and when the next last day to cancel before runtime is extended by maturity
        $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $this->valid_from);
        $endDate->subDay();

        // set defaults
        $maturity = $maturity_min = $this->product->maturity_min ?: Product::$maturity_min;
        $pon = $this->product->period_of_notice ?: Product::$pon;

        // add minimum maturity and set endDate to last of month as default if no maturity is specified
        // TODO?: Always set to end of month?
        $endDate = self::add_period($endDate, $maturity_min);
        if (! $this->product->maturity) {
            $endDate->lastOfMonth();
        }
        $invoiceDate = \Carbon\Carbon::createFromTimestamp(strtotime($date));
        $firstPonDate = self::sub_period(clone $endDate, $pon);

        // add maturity until endDate is after first possible date of period of notice
        if ($invoiceDate->gte($firstPonDate)) {
            $maturity = $this->product->maturity ?: Product::$maturity;
            do {
                $endDate = self::add_period($endDate, $maturity);
                $firstPonDate = self::sub_period(clone $endDate, $pon);
            } while ($invoiceDate->gte($firstPonDate));
        }

        // return end_of_term and (last) cancelation_day
        $ret['cancelation_day'] = $firstPonDate->toDateString();
        $ret['end_of_term'] = $endDate->toDateString();
        $ret['maturity'] = $maturity;

        return $ret;
    }

    /**
     * Add a time period to a time object
     *
     * @author Nino Ryschawy
     *
     * @param object 	current datetime
     * @param string 	e.g. 14D|3M|1Y (14 days|3 month|1 year)
     * @param string 	subtract or add time period
     */
    public static function add_period(\Carbon\Carbon $dt, $period, $method = 'add')
    {
        // split nr from timespan
        $nr = preg_replace('/[^0-9]/', '', $period);
        $span = str_replace($nr, '', $period);

        $d = $dt->__get('day');
        $m = $dt->__get('month');

        switch ($span) {
            case 'D':
                $dt->{$method.'Day'}($nr); break;

            case 'M':
                // handle last day of february
                $is_last = ($m == 2) && (($d == 28 && ! $dt->isLeapYear()) || ($d == 29 && ! $dt->isLeapYear()));

                $dt->{$method.'MonthNoOverflow'}($nr);

                if ($is_last) {
                    $dt->lastOfMonth();
                }

                break;

            case 'Y':
                // handle last day of february
                if ($d == 29 && $m == 2 && ($nr % 4)) {
                    $dt->subDay();
                }

                $dt->{$method.'Year'}($nr);

                break;
        }

        return $dt;
    }

    public static function sub_period(\Carbon\Carbon $dt, $period)
    {
        return self::add_period($dt, $period, 'sub');
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
        $item->valid_to = $item->valid_to ?: null;
        $tariff = $item->contract->get_valid_tariff($item->product->type);

        // set end date of old tariff to starting date of new tariff
        if (in_array($item->product->type, ['Internet', 'Voip', 'TV'])) {
            if ($tariff) {
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
        // this is ab(used) here for easily setting the correct values
        if (in_array($item->product->type, ['Internet', 'Voip'])) {
            $item->contract->daily_conversion();
        }

        // on enabled envia module: check if data has to be changed via envia TEL API
        if (\Module::collections()->has('ProvVoipEnvia')) {
            $purchase_tariff = $item->contract->purchase_tariff;
            $next_purchase_tariff = $item->contract->next_purchase_tariff;
            $voip_id = $item->contract->voip_id;
            $next_voip_id = $item->contract->next_voip_id;
            if ($next_purchase_tariff && ($purchase_tariff != $next_purchase_tariff)) {
                \Session::push('tmp_warning_above_form', 'ATTENTION: You have to “Change purchase tariff” (envia TEL API), too!');
            }
            if ($next_voip_id && ($voip_id != $next_voip_id)) {
                \Session::push('tmp_warning_above_form', 'ATTENTION: You have to “Change tariff” (envia TEL API), too!');
            }
        }

        if ($item->credit_amount < 0) {
            \Session::put('alert.warning', trans('messages.item_credit_amount_negative'));
        }

        // TODO: warn user if end_of_term is now earlier by adding this item ?
    }

    public function updating($item)
    {
        if (! $item->observer_enabled) {
            return;
        }

        // this doesnt work in prepare_input() !!
        $item->valid_to = $item->valid_to ?: null;

        // set end date of old tariff to starting date of new tariff (if it's not the same)
        if (in_array($item->product->type, ['Internet', 'Voip', 'TV'])) {
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

                Item::where('id', $tariff->id)->update([
                    'valid_to' => date('Y-m-d', strtotime('-1 day', strtotime($item->valid_from))),
                    'valid_to_fixed' => $item->valid_from_fixed || $tariff->valid_to_fixed ? true : false,
                    ]);
            }
        }

        // set end date for products with fixed number of cycles
        $this->handle_fixed_cycles($item);
    }

    public function updated($item)
    {
        if (! $item->observer_enabled) {
            return;
        }

        // Check if yearly charged item was already charged - maybe customer should get a credit then
        if ($item->isDirty('valid_to') && $item->product->proportional && $item->product->billing_cycle == 'Yearly' &&
            ($item->payed_month != 0 && date('Y', strtotime($item->valid_to)) == date('Y')) ||
                (date('m') == '01' && $item->valid_to != date('Y-12-31', strtotime('last year')) &&
                    date('Y-m', strtotime($item->valid_to)) == date('Y-12', strtotime('last year'))
                    )
            ) {
            \Session::put('alert.warning', trans('messages.iteM.concede_credit'));
        }

        // this is ab(used) here for easily setting the correct values
        if ($item->observer_dailyconversion) {
            // Only call for Internet & Voip Items
            if (in_array($item->product->type, ['Internet', 'Voip']) ||
                    ($item->isDirty('product_id') && in_array(Product::where('id', $item->getOriginal()['product_id'])->first()->type, ['Internet', 'Voip']))) {
                $item->contract->daily_conversion();
            }
        }
    }

    public function deleted($item)
    {
        // this is ab(used) here for easily setting the correct values
        if (in_array($item->product->type, ['Internet', 'Voip'])) {
            $item->contract->daily_conversion();
        }
    }

    /**
     * Auto fills valid_from and valid_to fields for items of products with fixed cycle count
     */
    private function handle_fixed_cycles($item)
    {
        if (! $item->product->cycle_count) {
            return;
        }

        $cnt = $item->product->cycle_count;
        if ($item->product->billing_cycle == 'Quarterly') {
            $cnt *= 3;
        }
        if ($item->product->billing_cycle == 'Yearly') {
            $cnt *= 12;
        }

        if (! $item->valid_from || $item->valid_from == '0000-00-00') {
            $item->valid_from = date('Y-m-d');
        }

        $item->valid_to = date('Y-m-d', strtotime('last day of this month', strtotime("+$cnt month", strtotime($item->valid_from))));
    }
}
