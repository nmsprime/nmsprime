<?php

namespace Modules\Dashboard\Entities;

use Module;
use Storage;
use Modules\ProvBase\Entities\Contract;

class BillingAnalysis
{
    /**
     * Get all today valid contracts
     *
     * @return mixed
     */
    public static function getValidContracts()
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
    private function generateReferenceDate($period = null, $days = null)
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
     * @return array    [chart => Array, total => Integer]
     */
    public static function getContractData()
    {
        if (Storage::disk('chart-data')->has('contracts.json') === false) {
            $content = json_encode(\Config::get('dashboard.contracts'));
        } else {
            $content = Storage::disk('chart-data')->get('contracts.json');
        }

        $array = json_decode($content, true);
        $data = array_slice($array, 0, 1);
        $data['chart'] = array_slice($array, 1, 5);

        if (Module::collections()->has('BillingBase')) {
            $data['csv'] = array_slice($array, 6, 2) + array_slice($array, 9);
            $data['table'] = array_slice($array, 8, 1);
        }

        if (self::checkJson($data) == true) {
            return self::getContractData();
        }

        return $data;
    }

    /**
     * Creates CSV from JSON(monthly: new customers, cancellations and balance) for the past and current year.
     *
     * @author Roy Schneider
     */
    public static function monthlyCustomersCsv()
    {
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

        array_unshift($headings, 'New items');
        fputcsv($file, $headings);

        $k = 0;
        $field = ['+ Internet', '- Internet', '+ Voip', '- Voip', '+ TV', '- TV'];
        foreach ([$data['monthly']['gain']['internet'], $data['monthly']['loss']['internet'], $data['monthly']['gain']['voip'], $data['monthly']['loss']['voip'], $data['monthly']['gain']['tv'], $data['monthly']['loss']['tv']] as $numbers) {
            array_unshift($numbers, $field[$k++]);
            fputcsv($file, $numbers);
        }

        array_unshift($data['monthly']['ratio'], ' Ratio');
        fputcsv($file, $data['monthly']['ratio']);
        fputcsv($file, ['']);
        array_unshift($data['new'], ' New Customers');
        fputcsv($file, $data['new']);
        array_unshift($data['canceled'], ' Cancellations');
        fputcsv($file, $data['canceled']);
        fclose($file);
        header('Content-Type: application/excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$fileName.'.csv"');
    }

    /**
     * Count contracts for given time interval
     *
     * @param array $contracts
     * @param string $date_interval_start
     * @return int
     */
    private static function countContracts($date)
    {
        $ret = 0;

        // for 800 contracts this is approximately 4x faster - DB::table is again 5x faster than Eloquents Contract::count -> (20x faster)
        $ret = Contract::where('contract_start', '<=', $date)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($date) {
                $query
                ->where('contract_end', '>=', $date)
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
    public static function getIncomeTotal()
    {
        $ret = [];
        $contracts = self::getValidContracts();
        $total = 0;

        // manipulate dates array for charge calculation for coming month (not last one)
        $conf = \Modules\BillingBase\Entities\BillingBase::first();
        $dates = \Modules\BillingBase\Console\SettlementRunCommand::create_dates_array();

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
    public static function saveIncomeToJson()
    {
        $income = self::getIncomeTotal();
        $income = self::formatChartDataIncome($income);

        Storage::disk('chart-data')->put('income.json', json_encode($income));
    }

    /**
     * Check if Json key exists
     *
     * @author Roy Schneider
     * @param array
     * @return void
     */
    public static function checkJson($data)
    {
        if (! array_key_exists('total', $data)) {
            return self::saveContractsToJson();
        }
    }

    /**
     * Determine the total count of contracts (Internet only, Voip only, Internet + Voip) for current & last year
     * Format and save to json.
     * Note: used by cronjob
     *
     * @author Nino Ryschawy, Roy Schneider
     */
    public static function saveContractsToJson()
    {
        $queries = [
            'Internet_only'     => ['Internet', 'not', 'Voip'],
            'Voip_only'         => ['Voip', 'not', 'Internet'],
            'Internet_and_Voip' => ['Internet', '', 'Voip'],
            ];

        $date = date('Y-m-d');
        $contracts['total'] = self::countContracts($date);

        // date array to count items for the line chart
        for ($i = 11; $i >= 0; $i--) {
            $time = \Carbon\Carbon::now()->subMonthNoOverflow($i);
            $date = $time->lastOfMonth()->format('Y-m-d');
            $contracts['labels'][] = $time->lastOfMonth()->format('Y-m-d');
            $contracts['contracts'][] = self::countContracts($date);

            if (Module::collections()->has('BillingBase')) {
                foreach ($queries as $name => $combinations) {
                    $contracts[$name][] = self::getContractCount($date, $combinations);
                }
            }
        }

        if (! Module::collections()->has('BillingBase')) {
            return self::saveDataToStorage($contracts);
        }

        $base = \DB::table('contract')
            ->join('item', 'item.contract_id', 'contract.id')
            ->join('product', 'product.id', 'item.product_id')
            ->where('contract.create_invoice', 1)
            ->where('item.valid_from_fixed', 1)
            ->whereNull('item.deleted_at')
            ->whereNull('contract.deleted_at');

        // date array to count all items of type [internet, voip, tv] for each month from January last year to today for the CSV
        for ($i = 11 + date('m'); $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonthNoOverflow($i);
            $start = $date->startOfMonth()->format('Y-m-d');
            $end = $date->endOfMonth()->format('Y-m-d');
            $all['monthly'][] = [$start, $end];
            $month['last'] = $end;
            $month['first'] = $start;
            $contracts['new'][] = self::getNewCustomerCount($month);
            $contracts['canceled'][] = self::getCancelationCount($month);
        }

        $tmp = \Carbon\Carbon::now()->startOfWeek();

        // date array to count all items of type [internet, voip, tv] for the last 4 weeks for the table
        for ($i = 0; $i < 4; $i++) {
            $all['weekly'][] = [$tmp->format('Y-m-d'), $tmp->copy()->endOfWeek()->format('Y-m-d')];
            $contracts['weekly']['week'][] = $tmp->format('W');
            $tmp->subWeek();
        }

        // calculate new items/customer every week or month
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

                    // cancellations every week or month
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

                // weekly and monthly balance
                foreach (array_keys($contracts[$span]['gain'][$type]) as $key) {
                    $contracts[$span]['ratio'][$key] = ($contracts[$span]['gain']['internet'][$key]
                        + $contracts[$span]['gain']['voip'][$key]
                        + $contracts[$span]['gain']['tv'][$key])
                        - ($contracts[$span]['loss']['internet'][$key]
                        + $contracts[$span]['loss']['voip'][$key]
                        + $contracts[$span]['loss']['tv'][$key]);
                }
            }
        }

        return self::saveDataToStorage($contracts);
    }

    private static function saveDataToStorage($contracts)
    {
        return Storage::disk('chart-data')->put('contracts.json', json_encode($contracts));
    }

    /**
     * Get count of contracts
     *
     * @param array     [first, last] day of month
     * @param array
     * @author Nino Ryschawy
     */
    public static function getContractCount($date, $combinations)
    {
        $filter = function ($query) use ($date) {
            $query
            ->where('contract.create_invoice', 1)
            ->whereNull('contract.deleted_at')
            ->where('contract_start', '<=', $date)
            ->where(function ($query) use ($date) {
                $query
                ->whereNull('contract_end')
                ->orWhere('contract_end', '=', '0000-00-00')
                ->orWhere('contract_end', '>=', $date);
            })
            ->whereNull('i.deleted_at')
            ->where('i.valid_from_fixed', 1)
            ->where('i.valid_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query
                ->whereNull('i.valid_to')
                ->orWhere('i.valid_to', '=', '0000-00-00')
                ->orWhere('i.valid_to', '>=', $date);
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
    public static function getIncomeData()
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
     * Returns rehashed data for the bar chart
     *
     * @param array $income
     * @return array
     */
    private static function formatChartDataIncome(array $income)
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
}
