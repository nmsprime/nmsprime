<?php

namespace Modules\Dashboard\Http\Controllers;

use View;
use App\GuiLog;
use App\Http\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * @return Obj 	View: Dashboard (index.blade)
     */
    public function index()
    {
        $title = 'Dashboard';

        $logs = GuiLog::where([['username', '!=', 'cronjob'], ['model', '!=', 'User']])->orderBy('updated_at', 'desc')->limit(50)->get();

        return View::make('dashboard::index', $this->compact_prep_view(compact('title', 'logs')));
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
}
