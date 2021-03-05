<?php

namespace Modules\ProvBase\Http\Controllers;

use View;
use Nwidart\Modules\Facades\Module;
use Modules\ProvBase\Entities\Contract;
use App\Http\Controllers\BaseController;

class ProvBaseController extends BaseController
{
    public function index()
    {
        $title = 'Provisioning Dashboard';

        $contracts_data = [];
        if (Module::collections()->has('BillingBase')) {
            $contracts_data = \Modules\BillingBase\Helpers\BillingAnalysis::getContractData();
        } else {
            $contracts_data['total'] = Contract::where('contract_start', '<=', date('Y-m-d'))
                ->where(whereLaterOrEqual('contract_end', date('Y-m-d')))
                ->count();
        }

        return View::make('provbase::index', $this->compact_prep_view(compact('title', 'contracts_data')));
    }

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        // label has to be the same like column in sql table
        return [
            ['form_type' => 'ip', 'name' => 'provisioning_server', 'description' => 'Provisioning Server IP'],
            ['form_type' => 'text', 'name' => 'ro_community', 'description' => 'SNMP Read Only Community'],
            ['form_type' => 'text', 'name' => 'rw_community', 'description' => 'SNMP Read Write Community'],

            ['form_type' => 'text', 'name' => 'domain_name', 'description' => 'Domain Name for Modems'],
            ['form_type' => 'text', 'name' => 'notif_mail', 'description' => 'Notification Email Address'],
            ['form_type' => 'text', 'name' => 'dhcp_def_lease_time', 'description' => 'DHCP Default Lease Time'],
            ['form_type' => 'text', 'name' => 'dhcp_max_lease_time', 'description' => 'DHCP Max Lease Time'],
            ['form_type' => 'text', 'name' => 'ppp_session_timeout', 'description' => 'PPP Session-Timeout', 'help' => trans('helper.ppp_session_timeout')],
            ['form_type' => 'text', 'name' => 'max_cpe', 'description' => 'Max CPEs per Modem', 'help' => 'Minimum & Default: 2'],
            ['form_type' => 'text', 'name' => 'ds_rate_coefficient', 'description' => 'Downstream rate coefficient', 'help' => trans('helper.rate_coefficient')],
            ['form_type' => 'text', 'name' => 'us_rate_coefficient', 'description' => 'Upstream rate coefficient', 'help' => trans('helper.rate_coefficient')],

            ['form_type' => 'text', 'name' => 'startid_contract', 'description' => 'Start ID Contracts'],
            ['form_type' => 'text', 'name' => 'startid_modem', 'description' => 'Start ID Modems'],
            ['form_type' => 'text', 'name' => 'startid_endpoint', 'description' => 'Start ID Endpoints'],

            ['form_type' => 'text', 'name' => 'acct_interim_interval', 'description' => 'Acct-Interim-Interval', 'help' => trans('helper.acct_interim_interval')],

            ['form_type' => 'checkbox', 'name' => 'modem_edit_page_new_tab', 'description' => 'Opening Modem Edit Page in New Tab', 'help' => trans('helper.openning_new_tab_for_modem')],
            ['form_type' => 'checkbox', 'name' => 'multiple_provisioning_systems', 'description' => 'Multiple provisioning systems', 'help' => 'Check if there are other DHCP servers in your network'],
            ['form_type' => 'checkbox', 'name' => 'additional_modem_reset', 'description' => 'Additional modem reset button', 'help' => trans('helper.additional_modem_reset')],
            ['form_type' => 'checkbox', 'name' => 'random_ip_allocation', 'description' => 'Allocate PPPoE IPs randomly'],
            ['form_type' => 'checkbox', 'name' => 'auto_factory_reset', 'description' => 'Automatic factory reset', 'help' => trans('helper.auto_factory_reset')],
        ];
    }

    /**
     * Show error message when user clicks on analysis page and ProvMon module is not installed/active
     *
     * @author Nino Ryschawy
     * @return View
     */
    public function missingProvMon()
    {
        $error = '501';
        $message = trans('messages.missingProvMon');

        return \View::make('errors.generic', compact('error', 'message'));
    }
}
