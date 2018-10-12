<?php

namespace Modules\Dashboard\Http\Controllers;

use Log;
use Auth;
use View;
use Module;
use Storage;
use Modules\ProvBase\Entities\Contract;
use App\Http\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * @return Obj 	View: Dashboard (index.blade)
     */
    public function index()
    {
        $title = 'Dashboard';
        $netelements = $services = [];
        $view = self::_get_view_permissions();

        if ($view['contracts']) {
            $data['contracts'] = self::get_contract_data();
        }

        if ($view['income']) {
            $data['income'] = self::get_income_data();
        }

        // TODO: add panel with table of tickets
        if ($view['tickets']) {
            $data['tickets']['table'] = self::get_new_tickets();
            $data['tickets']['total'] = count($data['tickets']['table']);
        }

        if ($view['hfc']) {
            $netelements = $this->_get_impaired_netelements();
            $services = $this->_get_impaired_services();
        }

        $data['news'] = $this->news();

        return View::make('dashboard::index', $this->compact_prep_view(compact('title', 'data', 'view', 'netelements', 'services')));
    }

    /**
     * Return Array of boolean values of different categories that shall (/not) be shown in dashboard (index.blade)
     */
    private static function _get_view_permissions()
    {
        $user = Auth::user();

        $view = [
            'date'          => true,
            'provvoipenvia' => false,
            'income'        => (Module::collections()->has('BillingBase') &&
                               $user->can('see income chart')),
            'contracts'     => (Module::collections()->has('ProvBase') &&
                               $user->can('view', \Modules\ProvBase\Entities\Contract::class)),
            'tickets'       => (Module::collections()->has('Ticketsystem') &&
                               $user->can('view', \Modules\Ticketsystem\Entities\Ticket::class)),
            'hfc'           => (Module::collections()->has('HfcReq') &&
                               $user->can('view', \Modules\HfcReq\Entities\NetElement::class)),
        ];

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
            ->where(function ($query) {
                $query
                ->where('contract_end', '>', date('Y-m-d'))
                ->orWhere('contract_end', '=', '0000-00-00')
                ->orWhereNull('contract_end');
            })
            ->orderBy('id');

        return Module::collections()->has('BillingBase') ? $query->with('items', 'items.product')->get()->all() : $query->get()->all();
    }

    /**
     * Generate date by given period
     *
     * @param string $period
     * @param int $days
     * @return false|string
     */
    private function generate_reference_date($period = null, $days = null)
    {
        if (is_null($period)) {
            return date('Y-m-d');
        }

        $month = date('m');
        $year = date('Y');

        switch ($period) {
            case 'lastMonth':
                $time = strtotime('last month');
                $ret = date('Y-m-'.date('t', $time), $time);
                break;

            case 'dayPeriod':
                $ret = date('Y-m-d', strtotime("-$days days"));
                break;
        }

        return $ret;
    }

    /**
     * Returns rehashed data for the line chart, total number of contracts for the widget and format the data for the table
     *
     * @return array 	[chart => Array, total => Integer]
     */
    private static function get_contract_data()
    {
        if (Storage::disk('chart-data')->has('contracts.json') === false) {
            $content = json_encode(\Config::get('dashboard.contracts'));
        } else {
            $content = Storage::disk('chart-data')->get('contracts.json');
        }

        $array = json_decode($content, true);
        $data['chart'] = array_slice($array, 0, 5);
        $data['csv'] = array_slice($array, 5, 2) + array_slice($array, 8);
        $data['table'] = array_slice($array, 7, 1);
        $data['total'] = end(json_decode($content)->contracts);

        return $data;
    }

    /**
     * Creates CSV from JSON(monthly: new customers, cancellations and balance) for the past and current year.
     *
     * @author Roy Schneider
     */
    public static function monthly_customers_csv()
    {
        foreach (['total', 'new_items', 'new_customers', 'cancellations'] as  $trans) {
            $$trans = trans("messages.$trans");
        }

        $content = Storage::disk('chart-data')->get('contracts.json');
        $array = json_decode($content, true);
        $data = array_slice($array, 5);

        $year = [date('Y', strtotime('-1 year')), date('Y', strtotime('this year'))];
        $fileName = implode('_', $year);
        $file = fopen('php://output', 'w');

        for ($i = 11 + date('m'); $i >= 0; $i--) {
            $temp = \Carbon\Carbon::now()->subMonthNoOverflow($i);
            $headings[] = $temp->format('Y/m');
        }

        array_unshift($headings, $new_items);
        fputcsv($file, $headings, ';');

        foreach ($data['monthly']['loss'] as $array_key => $arrays) {
            foreach ($arrays as $key => $value) {
                $data['monthly']['loss']["$array_key"][$key] = -1 * $value;
            }
        }

        $k = 0;
        $field = ['Internet+', 'Internet-', 'Voip+', 'Voip-', 'TV+', 'TV-'];
        foreach ([$data['monthly']['gain']['internet'], $data['monthly']['loss']['internet'], $data['monthly']['gain']['voip'], $data['monthly']['loss']['voip'], $data['monthly']['gain']['tv'], $data['monthly']['loss']['tv']] as $numbers) {
            array_unshift($numbers, $field[$k++]);
            fputcsv($file, $numbers, ';');
        }

        array_unshift($data['monthly']['ratio'], $total);
        fputcsv($file, $data['monthly']['ratio'], ';');
        fputcsv($file, ['']);
        array_unshift($data['new'], $new_customers);
        fputcsv($file, $data['new'], ';');
        array_unshift($data['canceled'], $cancellations);
        fputcsv($file, $data['canceled'], ';');
        fclose($file);
        header('Content-Type: application/excel; charset=utf-8'); //application/excel
        header('Content-Disposition: attachment; filename="'.$fileName.'.csv"');
    }

    /**
     * Count contracts for given time interval
     *
     * @param array $contracts
     * @param string $date_interval_start
     * @return int
     */
    private static function count_contracts($date_interval)
    {
        $ret = 0;

        // for 800 contracts this is approximately 4x faster - DB::table is again 5x faster than Eloquents Contract::count -> (20x faster)
        $ret = \DB::table('contract')->where('contract_start', '<=', $date_interval['first'])
            ->whereNull('deleted_at')
            ->where(function ($query) use ($date_interval) {
                $query
                ->where('contract_end', '>=', $date_interval['last'])
                ->orWhere('contract_end', '=', '0000-00-00')
                ->orWhereNull('contract_end');
            })
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
        $conf = \Modules\BillingBase\Entities\BillingBase::first();
        $dates = \Modules\BillingBase\Console\accountingCommand::create_dates_array();

        $dates['lastm_Y'] = date('Y-m');
        $dates['lastm_01'] = date('Y-m-01');
        $dates['thism_01'] = date('Y-m-01', strtotime('next month'));
        $dates['lastm'] = date('m');
        $dates['Y'] = date('Y');
        $dates['m'] = date('m', strtotime('next month'));

        foreach ($contracts as $c) {
            if (! $c->costcenter || ! $c->create_invoice) {
                continue;
            }

            $c->expires = date('Y-m-01', strtotime($c->contract_end)) == $dates['lastm_01'];

            foreach ($c->items as $item) {
                if (! isset($item->product)) {
                    continue;
                }

                $item->calculate_price_and_span($dates, false, false);
                $cycle = $item->get_billing_cycle();

                $total += $item->charge;

                // why cycle ?? - TODO: simplify
                if (! isset($ret[$item->product->type][$cycle])) {
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

        Storage::disk('chart-data')->put('income.json', json_encode($income));
    }

    /**
     * Determine the total count of contracts (Internet only, Voip only, Internet + Voip) for current & last year
     * Format and save to json.
     * Note: used by cronjob
     *
     * @author Nino Ryschawy, Roy Schneider
     */
    public static function save_contracts_to_json()
    {
        $queries = [
            'Internet_only'     => ['Internet', 'not', 'Voip'],
            'Voip_only'         => ['Voip', 'not', 'Internet'],
            'Internet_and_Voip' => ['Internet', '', 'Voip'],
            ];

        //date array to count items for the line chart
        for ($i = 11; $i >= 0; $i--) {
            $time = \Carbon\Carbon::now()->subMonthNoOverflow($i);
            $month['last'] = $time->lastOfMonth()->format('Y-m-d');
            $month['first'] = $time->firstOfMonth()->format('Y-m-d');
            $contracts['labels'][] = $time->lastOfMonth()->format('Y-m-d');
            $contracts['contracts'][] = self::count_contracts($month);

            foreach ($queries as $name => $combinations) {
                $contracts[$name][] = self::getContractCount($month, $combinations);
            }
        }

        $base = \DB::table('contract')
            ->join('item', 'item.contract_id', 'contract.id')
            ->join('product', 'product.id', 'item.product_id')
            ->where('contract.create_invoice', 1)
            ->where('item.valid_from_fixed', 1)
            ->whereNull('item.deleted_at')
            ->whereNull('contract.deleted_at');

        //date array to count all items of type [internet, voip, tv] for each month from January last year to today
        $dayOne = new \Carbon\Carbon('first day of January 0000');
        for ($i = 11 + date('m'); $i >= 0; $i--) {
            $tmp = \Carbon\Carbon::now()->startOfMonth()->subMonthNoOverflow($i);
            $all['monthly'][] = [$tmp->format('Y-m-d'), $tmp->endOfMonth()->format('Y-m-d')];
            $month['last'] = $tmp->lastOfMonth()->format('Y-m-d');
            $month['first'] = $tmp->firstOfMonth()->format('Y-m-d');
            $contracts['new'][] = self::getNewCustomerCount($month);
            $contracts['canceled'][] = self::getCancelationCount($month);
        }

        //date array to count all items of type [internet, voip, tv] for the current and the last 3 weeks
        $tmp = \Carbon\Carbon::now()->startOfWeek();
        for ($i = 0; $i < 4; $i++) {
            $all['weekly'][] = [$tmp->format('Y-m-d'), $tmp->copy()->endOfWeek()->format('Y-m-d')];
            $contracts['weekly']['week'][] = $tmp->format('W');
            $tmp->subWeek();
        }

        //calculate new items/customer every week or month
        foreach ($all as $span => $dates) {
            foreach ($dates as $date) {
                foreach (['internet', 'voip', 'tv', 'other'] as $type) {
                    $contracts[$span]['gain'][$type][] = with(clone $base)
                        ->where('contract.contract_start', '<=', $date[1])
                        ->where('product.type', $type)
                        ->where(function ($query) use ($date) {
                            $query
                            ->where('item.valid_from', '>=', $date[0])
                            ->where('item.valid_from', '<=', $date[1]);
                        })
                        ->where(function ($query) use ($date) {
                            $query
                            ->where('contract.contract_end', '>=', $date[1])
                            ->orWhere('contract.contract_end', '0000-00-00')
                            ->orWhereNull('contract.contract_end');
                        })
                        ->where(function ($query) use ($date) {
                            $query
                            ->where('item.valid_to', '>=', $date[1])
                            ->orWhere('item.valid_to', '0000-00-00')
                            ->orWhereNull('item.valid_to');
                        })
                        ->sum('item.count');

                    //cancellations every week or month
                    $contracts[$span]['loss'][$type][] = with(clone $base)
                        ->where('product.type', $type)
                        ->where('contract.contract_start', '<=', $date[1])
                        ->where(function ($query) use ($date) {
                            $query
                            ->where('contract.contract_end', '>=', $date[0])
                            ->where('contract.contract_end', '<=', $date[1])
                            ->orWhere(function ($query) use ($date) {
                                $query
                                ->where('item.valid_to', '>=', $date[0])
                                ->where('item.valid_to', '<=', $date[1]);
                            });
                        })->sum('item.count');
                }

                if ($span == 'weekly' || $span == 'monthly') {
                    //weekly and monthly balance
                    foreach (array_keys($contracts[$span]['gain'][$type]) as $key) {
                        $contracts[$span]['ratio'][$key] = ($contracts[$span]['gain']['internet'][$key] + $contracts[$span]['gain']['voip'][$key] + $contracts[$span]['gain']['tv'][$key]) - ($contracts[$span]['loss']['internet'][$key] + $contracts[$span]['loss']['voip'][$key] + $contracts[$span]['loss']['tv'][$key]);
                    }
                }
            }
        }

        Storage::disk('chart-data')->put('contracts.json', json_encode($contracts));
    }

    /**
     * Get count of contracts
     *
     * @param array     [first, last] day of month
     * @param array
     * @author Nino Ryschawy
     */
    public static function getContractCount($month, $combinations)
    {
        $filter = function ($query) use ($month) {
            $query
            ->where('contract.create_invoice', 1)
            ->whereNull('contract.deleted_at')
            ->where('contract_start', '<=', $month['last'])
            ->where(function ($query) use ($month) {
                $query
                ->whereNull('contract_end')
                ->orWhere('contract_end', '=', '0000-00-00')
                ->orWhere('contract_end', '>=', $month['last']);
            })
            ->whereNull('i.deleted_at')
            ->where('i.valid_from_fixed', 1)
            ->where('i.valid_from', '<=', $month['last'])
            ->where(function ($query) use ($month) {
                $query
                ->whereNull('i.valid_to')
                ->orWhere('i.valid_to', '=', '0000-00-00')
                ->orWhere('i.valid_to', '>=', $month['last']);
            });
        };

        return Contract::join('item as i', 'i.contract_id', '=', 'contract.id')
            ->join('product as p', 'i.product_id', '=', 'p.id')
            ->where('p.type', $combinations[0])
            ->where($filter)
            ->select('contract.number')
                ->{'where'.ucwords($combinations[1]).'In'}('contract.id', function ($query) use ($filter, $combinations) {
                    $query->from('contract')
                    ->join('item as i', 'i.contract_id', '=', 'contract.id')
                    ->join('product as p', 'i.product_id', '=', 'p.id')
                    ->where('p.type', $combinations[2])
                    ->where($filter)
                    ->select('contract.id');
                })->distinct()
                ->count();
    }

    /**
     * Get count of new customers
     *
     * @param array     [first, last] day of month
     * @return int
     *
     * @author Nino Ryschawy
     */
    public static function getNewCustomerCount($month)
    {
        $filter = function ($query) {
            $query
            ->where('contract.create_invoice', 1)
            ->whereNull('contract.deleted_at')
            ->whereNull('i.deleted_at')
            ->where('i.valid_from_fixed', 1)
            ->whereIn('p.type', ['Internet', 'Voip', 'TV']);
        };

        // Contract where (contract_start=month and item_start<=month) or (contract_start<=month and item_start=month and no item (tv, inet, voip) with valid_from in months before)
        return Contract::join('item as i', 'i.contract_id', '=', 'contract.id')
            ->join('product as p', 'i.product_id', '=', 'p.id')
            ->where($filter)
            ->where(function ($query) use ($month, $filter) {
                $query
                ->where(function ($query) use ($month) {
                    $query
                    ->where('contract_start', '<=', $month['last'])
                    ->where('contract_start', '>=', $month['first'])
                    ->where('i.valid_from', '<=', $month['last']);
                })
                ->orWhere(function ($query) use ($month, $filter) {
                    $query
                    ->where('contract_start', '<=', $month['last'])
                    ->where('i.valid_from', '<=', $month['last'])
                    ->where('i.valid_from', '>=', $month['first'])
                    ->whereNotIn('contract.id', function ($query) use ($month, $filter) {
                        $query
                        ->from('contract')
                        ->join('item as i', 'i.contract_id', '=', 'contract.id')
                        ->join('product as p', 'i.product_id', '=', 'p.id')
                        ->where($filter)
                        ->where('i.valid_from', '<', $month['first'])
                        ->select('contract.id');
                    });
                });
            })
            ->select('number')
            ->distinct('number')
            ->count('number');
    }

    /**
     * Get count of customers that canceled their contract
     *
     * @param array     [first, last] day of month
     * @return int
     */
    public static function getCancelationCount($month)
    {
        // Consider only contract_end, and contract must have had at least one item
        return Contract::join('item as i', 'i.contract_id', '=', 'contract.id')
            ->join('product as p', 'i.product_id', '=', 'p.id')
            ->where('contract.create_invoice', 1)
            ->whereNull('contract.deleted_at')
            ->whereNull('i.deleted_at')
            ->where('i.valid_from_fixed', 1)
            ->whereIn('p.type', ['Internet', 'Voip', 'TV'])
            ->where('contract_end', '<=', $month['last'])
            ->where('contract_end', '>=', $month['first'])
            ->select('number')
            ->distinct('number')
            ->count('number');
    }

    /**
     * Get chart data from json file - created by cron job
     *
     * @return array
     */
    public static function get_income_data()
    {
        if (Storage::disk('chart-data')->has('income.json') === false) {
            $content = json_encode(\Config::get('dashboard.income'));
        } else {
            $content = Storage::disk('chart-data')->get('income.json');
        }

        $data['chart'] = json_decode($content);

        $data['total'] = 0;
        foreach ($data['chart']->data as $value) {
            $data['total'] += $value;
        }

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
        if (\Module::collections()->has('HfcCustomer')) {
            $avg_critical_us = \Modules\HfcCustomer\Entities\ModemHelper::$avg_warning_us;
        }

        // Get only modems from valid contracts
        $query = \Modules\ProvBase\Entities\Modem::join('contract as c', 'c.id', '=', 'modem.contract_id')
            ->where('c.contract_start', '<=', date('Y-m-d'))
            ->where(function ($query) {
                $query
                ->whereNull('c.contract_end')
                ->orWhere('c.contract_end', '=', '0000-00-00')
                ->orWhere('c.contract_end', '>', date('Y-m-d', strtotime('last day')));
            });

        $modems = [
            'all' => $query->where('modem.id', '>', '0')->count(),
            'online' => $query->where('modem.us_pwr', '>', '0')->count(),
            'critical' => $query->where('modem.us_pwr', '>', $avg_critical_us)->count(),
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
        if (\Storage::disk('chart-data')->has('modems.json') === false) {
            return false;
        }

        if (! \Module::collections()->has('HfcCustomer')) {
            return false;
        }

        $a = json_decode(\Storage::disk('chart-data')->get('modems.json'));

        $a->text = 'Modems<br>'.$a->online.' / '.$a->all;

        $a->state = \Modules\HfcCustomer\Entities\ModemHelper::_ms_state($a->online, $a->all, 40);
        switch ($a->state) {
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
        $ret = [];
        $products = ['Internet', 'Voip', 'TV', 'Other'];

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
        if (! Module::collections()->has('Ticketsystem')) {
            return;
        }

        return Auth::user()->tickets()->where('state', '=', 'New')->get();
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

        if (! \Modules\HfcBase\Entities\IcingaObjects::db_exists()) {
            return $ret;
        }

        foreach (\Modules\HfcReq\Entities\NetElement::where('id', '>', '2')->get() as $element) {
            $state = $element->get_bsclass();
            if ($state == 'success' || $state == 'info') {
                continue;
            }
            if (! isset($element->icingaobjects->icingahoststatus) || $element->icingaobjects->icingahoststatus->problem_has_been_acknowledged || ! $element->icingaobjects->is_active) {
                continue;
            }

            $status = $element->icingaobjects->icingahoststatus;
            $link = link_to('https://'.\Request::server('HTTP_HOST').'/icingaweb2/monitoring/host/show?host='.$element->id, $element->name);
            $ret['clr'][] = $state;
            $ret['row'][] = [$link, $status->output, $status->last_time_up];
        }

        if ($ret) {
            $ret['hdr'] = ['Name', 'Status', 'since'];
        }

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

        if (! \Modules\HfcBase\Entities\IcingaObjects::db_exists()) {
            return $ret;
        }

        $objs = \Modules\HfcBase\Entities\IcingaObjects::join('icinga_servicestatus', 'object_id', '=', 'service_object_id')
            ->where('is_active', '=', '1')
            ->where('name2', '<>', 'ping4')
            ->where('last_hard_state', '<>', '0')
            ->where('problem_has_been_acknowledged', '<>', '1')
            ->orderByRaw("name2='ms_helper' desc")
            ->orderBy('last_time_ok', 'desc');

        foreach ($objs->get() as $service) {
            $tmp = \Modules\HfcReq\Entities\NetElement::find($service->name1);
            $link = link_to('https://'.\Request::server('HTTP_HOST').'/icingaweb2/monitoring/service/show?host='.$service->name1.'&service='.$service->name2, $tmp ? $tmp->name : $service->name1);
            $ret['clr'][] = $clr[$service->last_hard_state];
            $ret['row'][] = [$link, $service->name2, $service->output, $service->last_time_ok];
            $ret['perf'][] = self::_get_impaired_services_perfdata($service->perfdata);
        }

        if ($ret) {
            $ret['hdr'] = ['Host', 'Service', 'Status', 'since'];
        }

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
            if (substr($p[0], -1) == '%') {
                $p[3] = 0;
                $p[4] = 100;
            }
            $ret[$idx]['val'] = $p[0];
            // remove unit of measurement, such as percent
            $p[0] = preg_replace('/[^0-9.]/', '', $p[0]);

            // set the colour according to the current $p[0], warning $p[1] and critical $p[2] value
            $cls = null;
            if (isset($p[1]) && isset($p[2])) {
                $cls = self::_get_perfdata_class($p[0], $p[1], $p[2]);
                // don't show non-impaired perf data
                if ($cls == 'success') {
                    unset($ret[$idx]);
                    continue;
                }
            }
            $ret[$idx]['cls'] = $cls;

            // set the percentage according to the current $p[0], minimum $p[3] and maximum $p[4] value
            $per = null;
            if (isset($p[3]) && isset($p[4]) && ($p[4] - $p[3])) {
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
        if ($crit > $warn) {
            if ($cur < $warn) {
                return 'success';
            }
            if ($cur < $crit) {
                return 'warning';
            }
            if ($cur > $crit) {
                return 'danger';
            }
        } elseif ($crit < $warn) {
            if ($cur > $warn) {
                return 'success';
            }
            if ($cur > $crit) {
                return 'warning';
            }
            if ($cur < $crit) {
                return 'danger';
            }
        } else {
            return 'warning';
        }
    }

    /*
     * For News Blade:
     *
     * This function should guide a new user through critical stages
     * like installation. To do this, we should test how far installation
     * process is and addvice the next steps the user should do..
     *
     * This function could also be used to inform the user of new updates (etc)
     */
    public function news()
    {
        // check for insecure install
        if ($insecure = $this->isInsecureInstall()) {
            return $insecure;
        }

        // Install add sequence check
        if (\Module::collections()->has('ProvBase') && (\Modules\ProvBase\Entities\Modem::count() == 0)) {
            return $this->newsInstallAndSequenceCheck();
        }

        // Check for official news from support.nmsprime.com
        if ($news = $this->newsLoadOfficialSite()) {
            return $news;
        }

        // crowdin - check if language is still supported, otherwise show crowdin link
        if (! in_array(\Auth::user()->language, config('app.supported_locales'))) {
            return ['youtube' => 'https://www.youtube.com/embed/9mydbfHDDP4',
                    'text' => ' <li>NMS PRIME is not yet translated to your language. Help translating NMS PRIME with
                    <a href="https://crowdin.com/project/nmsprime/'.\Auth::user()->language.'" target="_blank">Crowdin</a></li>', ];
        }

        // links need to be in embedded style, like:
        //return ['youtube' => 'https://www.youtube.com/embed/9mydbfHDDP4',
        //		'text' => "You should do: <a href=https://lifeisgood.com>BlaBlaBla</a>"];
    }

    /*
     * For News Blade:
     *
     * Check if installation is secure
     */
    private function isInsecureInstall()
    {
        // change default psw's
        if (\Hash::check('toor', \Auth::user()->password)) {
            return ['youtube' => 'https://www.youtube.com/embed/TVjJ7T8NZKw',
                    'text' => '<li>Next: Change default Password! '.\HTML::linkRoute('User.profile', 'Global Config', \Auth::user()->id), ];
        }

        // check for insecure MySQL root password
        // This requires to run: mysql_secure_installation
        if (env('ROOT_DB_PASSWORD') == '') {
            try {
                \DB::connection('mysql-root')->getPdo();
                if (\DB::connection()->getDatabaseName()) {
                    return ['youtube' => 'https://www.youtube.com/embed/dZWjeL-LmG8',
                    'text' => '<li>Danger! Run: mysql_secure_installation in bash as root!', ];
                }
            } catch (\Exception $e) {
            }
        }

        // means: secure – nothing todo
    }

    /*
     * News panel: load news from support server to json file
     * Documentation panel: load documentation.json from support server
     *
     * Official News Parser
     */
    public function newsLoadToFile()
    {
        if (env('IGNORE_NEWS')) {
            return false;
        }

        // get actual network size based on SLA table
        $sla = \App\Sla::first();
        $support = 'https://support.nmsprime.com';

        $files = [
            'news.json' => "$support/news.php?ns=".urlencode($sla->get_sla_size()).'&sla='.urlencode($sla->name),
            'documentation.json' => "$support/documentation.json",
        ];

        foreach ($files as $name => $url) {
            try {
                Storage::put("data/dashboard/$name", file_get_contents($url));
            } catch (\Exception $e) {
                Log::error("Error retrieving $name (using installed version): ".$e->getMessage());
                Storage::delete("data/dashboard/$name");
            }
        }
    }

    /*
     * For News Blade:
     *
     * Official News Parser
     */
    private function newsLoadOfficialSite()
    {
        $file = 'data/dashboard/news.json';

        if (! Storage::exists($file)) {
            return;
        }

        $json = json_decode(Storage::get($file));

        if (! isset($json->youtube) || ! isset($json->text)) {
            return;
        }

        return ['youtube' => $json->youtube,
                'text' => $json->text, ];
    }

    /*
     * For News Blade:
     *
     * check install sequence order
     */
    private function newsInstallAndSequenceCheck()
    {
        // set ISP name
        if (! \GlobalConfig::first()->name) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s',
                    'text' => '<li>Next: Set Global Config ISP name: '.\HTML::linkRoute('Config.index', 'Global Config'), ];
        }

        // add CMTS
        if (\Modules\ProvBase\Entities\Cmts::count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=159&',
                    'text' => '<li>Next: '.\HTML::linkRoute('Cmts.create', 'Create CMTS'), ];
        }

        // add IP-Pools
        if (\Modules\ProvBase\Entities\IpPool::count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=240&',
                    'text' => '<li>Next: '.\HTML::linkRoute('Cmts.edit', 'Create IP-Pools',
                        \Modules\ProvBase\Entities\Cmts::first()), ];
        }

        // QoS
        if (\Modules\ProvBase\Entities\Qos::count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=380&',
                    'text' => '<li>Next: '.\HTML::linkRoute('Qos.create', 'Create QoS-Profile'), ];
        }

        // Product
        if (\Module::collections()->has('BillingBase') &&
            \Modules\BillingBase\Entities\Product::where('type', '=', 'Internet')->count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=425&',
                    'text' => '<li>Next: '.\HTML::linkRoute('Product.create', 'Create Billing Product'), ];
        }

        // Configfile
        if (\Modules\ProvBase\Entities\Configfile::where('device', '=', 'cm')->where('public', '=', 'yes')->count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=500&',
                    'text' => '<li>Next: '.\HTML::linkRoute('Configfile.create', 'Create Configfile'), ];
        }

        // add costcenter
        if (\Module::collections()->has('BillingBase') && \Modules\BillingBase\Entities\CostCenter::count() == 0) {
            return ['youtube' => null,
                    'text' => '<li>Next: '.\HTML::linkRoute('CostCenter.create', 'Create a first Cost Center'), ];
        }

        // add Contract
        if (\Modules\ProvBase\Entities\Contract::count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/t-PFsy42cI0?start=0&',
                    'text' => '<li>Congratulations: now you can create a first '.\HTML::linkRoute('Contract.create', 'Contract'), ];
        }

        // check if nominatim email address is set, otherwise osm geocoding won't be possible
        if (env('OSM_NOMINATIM_EMAIL') == '') {
            return ['text' => '<li>Next: Set an email address (OSM_NOMINATIM_EMAIL) in /etc/nmsprime/env/global.env to enable geocoding for modems</li>'];
        }

        // check for local nameserver
        preg_match('/^Server:\s*(\d{1,3}).\d{1,3}.\d{1,3}.\d{1,3}$/m', shell_exec('nslookup nmsprime.com'), $matches);
        if (isset($matches[1]) && $matches[1] != '127') {
            return ['text' => '<li>Next: Set your nameserver to 127.0.0.1 in /etc/resolv.conf and make sure it won\'t be overwritten via DHCP (see DNS and PEERDNS in /etc/sysconfig/network-scripts/ifcfg-*)</li>'];
        }

        // add Modem
        if (\Modules\ProvBase\Entities\Modem::count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/t-PFsy42cI0?start=40&',
                    'text' => '<li>Congratulations: now you can create a first '.\HTML::linkRoute('Contract.edit', 'Modem', \Modules\ProvBase\Entities\Contract::first()), ];
        }

        return false;
    }
}
