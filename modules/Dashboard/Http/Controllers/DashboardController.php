<?php

namespace Modules\Dashboard\Http\Controllers;

use View;
use Log;

use App\Http\Controllers\BaseController;
use Modules\ProvBase\Entities\Contract;
use Modules\Ticketsystem\Entities\Ticket;

class DashboardController extends BaseController
{

	/**
	 * @return Obj 	View: Dashboard (index.blade)
	 */
	public function index()
	{
		$title = 'Dashboard';
		$netelements = $services = array();
		$view = self::_get_view_permissions();

		if ($view['contracts'])
			$data['contracts'] = self::get_contract_data();

		if ($view['income'])
			$data['income'] = self::get_income_data();

		// TODO: add panel with table of tickets
		if ($view['tickets'])
		{
			$data['tickets']['table'] = self::get_new_tickets();
			$data['tickets']['total'] = count($data['tickets']['table']);
		}

		if ($view['hfc'])
		{
			$netelements = $this->_get_impaired_netelements();
			$services 	 = $this->_get_impaired_services();
		}
		return View::make('dashboard::index', $this->compact_prep_view(compact('title', 'data', 'view', 'netelements', 'services')));
	}


	/**
	 * Return Array of boolean values of different categories that shall (/not) be shown in dashboard (index.blade)
	 */
	private static function _get_view_permissions()
	{
		$role_ids_fix = array(
			'superadmin' 	=> 1,
			'director' 		=> 3,
			'technican' 	=> 4,
			'accounting' 	=> 5,
		);

		$income = $hfc = false;

		// check user permissions
		foreach (\Auth::user()->roles as $role)
		{
			if (\Module::collections()->has('BillingBase'))
			{
				if ($role->id == $role_ids_fix['director'])
					$income = true;
			}

			if (\Module::collections()->has('HfcReq'))
			{
				if ($role->id == $role_ids_fix['superadmin'] ||
					$role->id == $role_ids_fix['director'] ||
					$role->id == $role_ids_fix['technican'])
					$hfc = true;
			}
		}

		$view = array(
			'contracts' 	=> \Module::collections()->has('ProvBase'),
			'income'		=> $income,
			'tickets' 		=> \Module::collections()->has('Ticketsystem'),
			'provvoipenvia' => false,
			'date' 			=> true,
			'hfc' 			=> $hfc,
			);

		return $view;
	}


	/**
	 * Get all today valid contracts
	 *
	 * @return mixed
	 */
	public static function get_valid_contracts()
	{
		$query = Contract::where('contract_start', '<', date('Y-m-d'))
			->where(function ($query) { $query
				->where('contract_end', '>', date('Y-m-d'))
				->orWhere('contract_end', '=', '0000-00-00')
				->orWhereNull('contract_end');})
			->orderBy('id');

		return \Module::collections()->has('BillingBase') ? $query->with('items', 'items.product')->get()->all() : $query->get()->all();
	}


	/**
	 * Generate date by given period
	 *
	 * @param string $period
	 * @param integer $days
	 * @return false|string
	 */
	private function generate_reference_date($period = null, $days = null)
	{
		if (is_null($period))
			return date('Y-m-d');

		$month = date('m');
		$year = date('Y');

		switch ($period)
		{
			case 'lastMonth':
				$time = strtotime('last month');
				$ret  = date('Y-m-'.date('t', $time), $time);
				break;

			case 'dayPeriod':
				$ret = date('Y-m-d', strtotime("-$days days"));
				break;
		}

		return $ret;
	}

	/**
	 * Returns rehashed data for the line chart and total number of contracts for the widget
	 *
	 * @return Array 	[chart => Array, total => Integer]
	 */
	private static function get_contract_data()
	{
		if (\Storage::disk('chart-data')->has('contracts.json') === false) {
			$content = json_encode(\Config::get('dashboard.contracts'));
		} else {
			$content = \Storage::disk('chart-data')->get('contracts.json');
		}

		$data['chart'] = json_decode($content);
		$data['total'] = end($data['chart']->contracts);

		return $data;
	}

	/**
	 * Count contracts for given time interval
	 *
	 * @param array $contracts
	 * @param string $date_interval_start
	 * @return int
	 */
	private static function count_contracts($date_interval_start)
	{
		$ret = 0;

		// for 800 contracts this is approximately 4x faster - DB::table is again 5x faster than Eloquents Contract::count -> (20x faster)
		$ret = \DB::table('contract')->where('contract_start', '<=', $date_interval_start)
			->whereNull('deleted_at')
			->where(function ($query) { $query
				->where('contract_end', '>', date('Y-m-d'))
				->orWhere('contract_end', '=', '0000-00-00')
				->orWhereNull('contract_end');})
			->count();

		return $ret;
	}


	/**
	 * Returns monthly incomes for each product type
	 *
	 * @return array
	 */
	public static function get_income_total()
	{
		$ret = [];
		$contracts = self::get_valid_contracts();
		$total = 0;

		// manipulate dates array for charge calculation for coming month (not last one)
		$conf  = \Modules\BillingBase\Entities\BillingBase::first();
		$dates = \Modules\BillingBase\Console\accountingCommand::create_dates_array();

		$dates['lastm_Y'] 	= date('Y-m');
		$dates['lastm_01'] 	= date('Y-m-01');
		$dates['thism_01'] 	= date('Y-m-01', strtotime('next month'));
		$dates['lastm'] 	= date('m');
		$dates['Y'] 		= date('Y');
		$dates['m'] 		= date('m', strtotime('next month'));

		foreach ($contracts as $c)
		{
			if (!$c->costcenter || !$c->create_invoice)
				continue;

			$c->expires = date('Y-m-01', strtotime($c->contract_end)) == $dates['lastm_01'];

			foreach ($c->items as $item)
			{
				if (!isset($item->product))
					continue;

				$item->calculate_price_and_span($dates, false, false);
				$cycle = $item->get_billing_cycle();

				$total += $item->charge;

				// why cycle ?? - TODO: simplify
				if (!isset($ret[$item->product->type][$cycle])) {
					$ret[$item->product->type][$cycle] = $item->charge;
					continue;
				}

				$ret[$item->product->type][$cycle] += $item->charge;
			}
		}

		// Net income total - TODO: calculate gross ?
		$ret['total'] = $total;

		return $ret;
	}


	/**
	 * Calculate Income for current month, format and save to json
	 * Used by Cronjob
	 */
	public static function save_income_to_json()
	{
		$income = self::get_income_total();
		$income = self::format_chart_data_income($income);

		\Storage::disk('chart-data')->put('income.json', json_encode($income));
	}


	/**
	 * Calculate contracts for the last 12 months, format and save to json
	 * Used by cronjob
	 */
	public static function save_contracts_to_json()
	{
		$i = 12;
		$time = \Carbon\Carbon::create();

		while($i)
		{
			$labels[] 	 = $time->format('m/Y');
			$contracts[] = self::count_contracts($time->format('Y-m-01'));

			$time->subMonthNoOverflow();
			$i--;
		}

		$array = ['contracts' => array_reverse($contracts), 'labels' => array_reverse($labels)];

		\Storage::disk('chart-data')->put('contracts.json', json_encode($array));
	}

	/**
	 * Get chart data from json file - created by cron job
	 *
	 * @return array
	 */
	public static function get_income_data()
	{
		if (\Storage::disk('chart-data')->has('income.json') === false)
			$content = json_encode(\Config::get('dashboard.income'));
		else
			$content = \Storage::disk('chart-data')->get('income.json');

		$data['chart'] = json_decode($content);

		$data['total'] = 0;
		foreach ($data['chart']->data as $value)
			$data['total'] += $value;

		$data['total'] = (int) $data['total'];

		return $data;
	}


	/**
	 * Calculate modem statistics (online/offline), format and save to json
	 * Used by Cronjob
	 */
	public static function save_modem_statistics()
	{
		$avg_critical_us = 52;
		if (\Module::collections()->has('HfcCustomer'))
			$avg_critical_us = \Modules\HfcCustomer\Entities\ModemHelper::$avg_warning_us;

		// Get only modems from valid contracts
		$query = \Modules\ProvBase\Entities\Modem::join('contract as c', 'c.id', '=', 'modem.contract_id')
			->where('c.contract_start', '<=', date('Y-m-d'))
			->where(function ($query) { $query
				->whereNull('c.contract_end')
				->orWhere('c.contract_end', '=', '0000-00-00')
				->orWhere('c.contract_end', '>', date('Y-m-d', strtotime('last day')));
				});

		$modems = [
			'all' => $query->where('modem.id', '>', '0')->count(),
			'online' => $query->where('modem.us_pwr', '>', '0')->count(),
			'critical' => $query->where('modem.us_pwr', '>', $avg_critical_us)->count()
		];

		\Storage::disk('chart-data')->put('modems.json', json_encode($modems));
	}


	/**
	 * Get modem statistics (online/offline) from json file - created by cron job
	 *
	 * @return array
	 */
	public static function get_modem_statistics()
	{
		if (\Storage::disk('chart-data')->has('modems.json') === false)
			return false;

		if (!\Module::collections()->has('HfcCustomer'))
			return false;

		$a = json_decode( \Storage::disk('chart-data')->get('modems.json') );

		$a->text = 'Modems<br>'.$a->online.' / '.$a->all;

		$a->state = \Modules\HfcCustomer\Entities\ModemHelper::_ms_state($a->online, $a->all, 40);
		switch ($a->state)
		{
			case 'OK':			$a->fa = 'fa fa-thumbs-up'; $a->style = 'success'; break;
			case 'WARNING':		$a->fa = 'fa fa-meh-o'; $a->style = 'warning'; break;
			case 'CRITICAL':	$a->fa = 'fa fa-frown-o'; $a->style = 'danger'; break;

			default:
				$a->fa = 'fa-question';
		}


		return $a;
	}


	/**
	 * Returns rehashed data for the bar chart
	 *
	 * @param array $income
	 * @return array
	 */
	private static function format_chart_data_income(array $income)
	{
		$ret = array();
		$products = array('Internet', 'Voip', 'TV', 'Other');

		// TODO: why differentiate between monthly and yearly ??
		foreach ($products as $product) {

			if (array_key_exists($product, $income)) {
				if (isset($income[$product]['Monthly'])) {
					$data = $income[$product]['Monthly'];
				} elseif (isset($income[$product]['Yearly'])) {
					$data = $income[$product]['Yearly'];
				}
				$val = number_format($data, 2, '.', '');
			} else {
				$val = number_format(0, 2, '.', '');
			}

			if ($product == 'Other') {
				$product = \App\Http\Controllers\BaseViewController::translate_view($product, 'Dashboard');
			}

			$ret['data'][] = $val;
			$ret['labels'][] = $product;
		}

		return $ret;
	}

	/**
	 * Returns all tickets with state = new
	 *
	 * @return array
	 */
	private static function get_new_tickets()
	{
		if (!\Module::collections()->has('Ticketsystem'))
			return null;

		return \Auth::user()->tickets()->where('state', '=', 'New')->get();
	}


	/**
	 * Return all impaired netelements in a table array
	 *
	 * @author Ole Ernst
	 * @return array
	 *
	 * TODO: This function is actually the most timeconsuming while creating the dashboard index view
	 *	-> calculate in Background ? (comment by Nino Ryschawy 2017-11-21)
	 */
	private static function _get_impaired_netelements()
	{
		$ret = [];

		if(!\Modules\HfcBase\Entities\IcingaObjects::db_exists())
			return $ret;

		foreach(\Modules\HfcReq\Entities\NetElement::where('id', '>', '2')->get() as $element) {
			$state = $element->get_bsclass();
			if ($state == 'success' || $state == 'info')
				continue;
			if(!isset($element->icingaobjects->icingahoststatus) || $element->icingaobjects->icingahoststatus->problem_has_been_acknowledged || !$element->icingaobjects->is_active)
				continue;

			$status = $element->icingaobjects->icingahoststatus;
			$link = link_to('https://'.\Request::server('HTTP_HOST').'/icingaweb2/monitoring/host/show?host='.$element->id, $element->name);
			$ret['clr'][] = $state;
			$ret['row'][] = [$link, $status->output, $status->last_time_up];
		}

		if($ret)
			$ret['hdr'] = ['Name', 'Status', 'since'];

		return $ret;
	}


	/**
	 * Return all impaired services in a table array
	 *
	 * @author Ole Ernst
	 * @return array
	 */
	private static function _get_impaired_services()
	{
		$ret = [];
		$clr = ['success', 'warning', 'danger', 'info'];

		if(!\Modules\HfcBase\Entities\IcingaObjects::db_exists())
			return $ret;

		$objs = \Modules\HfcBase\Entities\IcingaObjects::join('icinga_servicestatus', 'object_id', '=', 'service_object_id')
			->where('is_active', '=', '1')
			->where('name2', '<>', 'ping4')
			->where('last_hard_state', '<>', '0')
			->where('problem_has_been_acknowledged', '<>', '1')
			->orderBy('last_time_ok', 'desc');

		foreach($objs->get() as $service) {
			$tmp = \Modules\HfcReq\Entities\NetElement::find($service->name1);
			$link = link_to('https://'.\Request::server('HTTP_HOST').'/icingaweb2/monitoring/service/show?host='.$service->name1.'&service='.$service->name2, $tmp ? $tmp->name : $service->name1);
			$ret['clr'][] = $clr[$service->last_hard_state];
			$ret['row'][] = [$link, $service->name2, $service->output, $service->last_time_ok];
			$ret['perf'][] = self::_get_impaired_services_perfdata($service->perfdata);
		}

		if($ret)
			$ret['hdr'] = ['Host', 'Service', 'Status', 'since'];

		return $ret;
	}


	/**
	 * Return formatted impaired performance data for a given perfdata string
	 *
	 * @author Ole Ernst
	 * @return array
	 */
	private static function _get_impaired_services_perfdata($perf)
	{
		$ret = [];
		preg_match_all("/('.+?'|[^ ]+)=([^ ]+)/", $perf, $matches, PREG_SET_ORDER);
		foreach ($matches as $idx => $val) {
			$ret[$idx]['text'] = $val[1];
			$p = explode(';', rtrim($val[2], ';'));
			// we are dealing with percentages
			if(substr($p[0], -1) == '%') {
				$p[3] = 0;
				$p[4] = 100;
			}
			$ret[$idx]['val'] = $p[0];
			// remove unit of measurement, such as percent
			$p[0] = preg_replace("/[^0-9.]/", "",$p[0]);

			// set the colour according to the current $p[0], warning $p[1] and critical $p[2] value
			$cls = null;
			if(isset($p[1]) && isset($p[2])) {
				$cls = self::_get_perfdata_class($p[0], $p[1], $p[2]);
				// don't show non-impaired perf data
				if($cls == 'success') {
					unset($ret[$idx]);
					continue;
				}
			}
			$ret[$idx]['cls'] = $cls;

			// set the percentage according to the current $p[0], minimum $p[3] and maximum $p[4] value
			$per = null;
			if(isset($p[3]) && isset($p[4]) && ($p[4] - $p[3])) {
				$per = ($p[0] - $p[3]) / ($p[4] - $p[3]) * 100;
				$ret[$idx]['text'] .= sprintf(' (%.1f%%)', $per);
			}
			$ret[$idx]['per'] = $per;

		}

		return $ret;
	}


	/**
	 * Return performance data colour class according to given limits
	 *
	 * @author Ole Ernst
	 * @return string
	 */
	private static function _get_perfdata_class($cur, $warn, $crit)
	{
		if($crit > $warn) {
			if($cur < $warn)
				return 'success';
			if($cur < $crit)
				return 'warning';
			if($cur > $crit)
				return 'danger';
		} elseif($crit < $warn) {
			if($cur > $warn)
				return 'success';
			if($cur > $crit)
				return 'warning';
			if($cur < $crit)
				return 'danger';
		} else {
			return 'warning';
		}
	}

}
