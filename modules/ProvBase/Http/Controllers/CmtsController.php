<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\ProvBase\Entities\Cmts;

class CmtsController extends \BaseController
{
    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        $init_values = [];

        if (! $model) {
            $model = new Cmts;
        }

        // create context: calc next free ip pool
        if (! $model->exists) {
            $init_values = [];

            // fetch all CMTS ip's and order them
            $ips = Cmts::where('id', '>', '0')->orderBy(\DB::raw('INET_ATON(ip)'))->get();

            // still CMTS added?
            if ($ips->count() > 0) {
                $next_ip = long2ip(ip2long($ips[0]->ip) - 1);
            } // calc: next_ip = last_ip-1
            else {
                $next_ip = env('CMTS_SETUP_FIRST_IP', '10.255.0.254');
            } // default first ip

            $init_values += [
                'ip' => $next_ip,
            ];
        }

        // CMTS type selection based on CMTS company
        if (\Input::has('company')) { // for auto reload
            $company = \Input::get('company');
        } elseif ($model->exists) { // else if using edit.blade
            $company = $model->company;
            $init_values += [
                'type' => $model->type,
            ];
        } else { // a fresh create
            $company = 'Cisco';
        }

        // The CMTS company and type Array
        $company_array = ['Cisco' => 'Cisco', 'Casa' => 'Casa', 'Arris' => 'Arris', 'Motorola' => 'Motorola', 'Other' => 'Other'];
        switch ($company) {
            case 'Cisco': $type = ['ubr7225' => 'ubr7225', 'ubr7246' => 'ubr7246', 'ubr10k' => 'ubr10k']; break;
            case 'Casa':  $type = ['C1G' => 'C1G', 'C2200' => 'C2200', 'C3200' => 'C3200', 'C100G']; break;
            case 'Arris': $type = ['C1000' => 'C1000', 'C3' => 'C3', 'C4' => 'C4', 'E6000' => 'E6000']; break;
            case 'Motorola': $type = ['BSR64000' => 'BSR64000', 'BSR2000' => 'BSR2000']; break;
            default: $type = ['Other' => 'Other']; break;
        }

        /**
         * label has to be the same like column in sql table
         */
        // TODO: type should be jquery based select depending on the company
        // TODO: State and Monitoring without functionality -> hidden
        $ret_tmp = [
            ['form_type' => 'select', 'name' => 'company', 'description' => 'Company', 'value' => $company_array],
            ['form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => $type],
            ['form_type' => 'text', 'name' => 'hostname', 'description' => 'Hostname'],
            ['form_type' => 'ip', 'name' => 'ip', 'description' => 'IP', 'help' => 'Online'],
            ['form_type' => 'text', 'name' => 'community_rw', 'description' => 'SNMP Private Community String'],
            ['form_type' => 'text', 'name' => 'community_ro', 'description' => 'SNMP Public Community String'],
            ['form_type' => 'text', 'name' => 'state', 'description' => 'State', 'hidden' => 1],
            ['form_type' => 'text', 'name' => 'monitoring', 'description' => 'Monitoring', 'hidden' => 1],
        ];

        // add init values if set
        $ret = [];
        foreach ($ret_tmp as $elem) {
            if (array_key_exists($elem['name'], $init_values)) {
                $elem['init_value'] = $init_values[$elem['name']];
            }
            array_push($ret, $elem);
        }

        return $ret;
    }

    protected function get_form_tabs($view_var)
    {
        if (! \Module::collections()->has('ProvMon')) {
            return [];
        }

        return [
            ['name' => 'Edit', 'route' => 'Cmts.edit', 'link' => [$view_var->id]],
            ['name' => 'Analysis', 'route' => 'ProvMon.cmts', 'link' => [$view_var->id]],
            parent::get_form_tabs($view_var)[0],
        ];
    }
}
