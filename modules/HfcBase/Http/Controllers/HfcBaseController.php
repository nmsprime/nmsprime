<?php

namespace Modules\HfcBase\Http\Controllers;

use View;
use Module;
use Bouncer;
use App\GuiLog;
use App\Http\Controllers\BaseController;
use Modules\Ticketsystem\Http\Controllers\TicketsystemController;

class HfcBaseController extends BaseController
{
    /**
     * Compose HFC Base Dashboard
     *
     * @return Illuminate\View\View
     */
    public function index()
    {
        $title = 'Detect Dashboard';
        $permissions = $this->getViewPermissions();

        $logs = GuiLog::where([['username', '!=', 'cronjob'], ['model', '!=', 'User']])
            ->whereIn('model', ['NetElement', 'NetElementType', 'MibFile', 'Mpr'])
            ->orderBy('updated_at', 'desc')->orderBy('user_id', 'asc')
            ->limit(20)->get();

        if ($permissions['detect']) {
            $impairedData = (new TroubleDashboardController())->summary();
        }

        if ($permissions['tickets']) {
            $tickets = TicketsystemController::dashboardData();
        }

        return View::make('HfcBase::index', $this->compact_prep_view(compact('title', 'impairedData', 'logs', 'tickets')));
    }

    /**
     * Return Array of boolean values for different categories that shall (not)
     * be shown on the Detect Dashboard (index blade)
     */
    private function getViewPermissions()
    {
        return [
            'detect'        => (Module::collections()->has('HfcBase') &&
                               Bouncer::can('view', \Modules\HfcBase\Entities\TreeErd::class)),
            'tickets'       => (Module::collections()->has('Ticketsystem') &&
                               Bouncer::can('view', \Modules\Ticketsystem\Entities\Ticket::class)),
        ];
    }

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        // label has to be the same like column in sql table
        $a = [
            ['form_type' => 'text', 'name' => 'ro_community', 'description' => 'SNMP Read Only Community'],
            ['form_type' => 'text', 'name' => 'rw_community', 'description' => 'SNMP Read Write Community', 'space' => 1],
        ];

        $b = [];
        if (Module::collections()->has('HfcSnmp')) {
            $b = [
                ['form_type' => 'text', 'name' => 'rkm_server', 'description' => 'RKM Server '.trans('messages.Address'), 'help' => trans('hfcsnmp::help.rkmServerAddress'), 'options' => ['placeholder' => '172.20.0.10:1700']],
                ['form_type' => 'text', 'name' => 'rkm_server_username', 'description' => 'RKM Server '.trans('messages.Username')],
                ['form_type' => 'text', 'name' => 'rkm_server_password', 'description' => 'RKM Server '.trans('messages.Password'), 'space' => 1],

                ['form_type' => 'text', 'name' => 'video_controller', 'description' => 'Video Controlling Server '.trans('messages.Address'), 'options' => ['placeholder' => '172.20.0.11:1701']],
                ['form_type' => 'text', 'name' => 'video_controller_username', 'description' => 'RKM Server '.trans('messages.Username')],
                ['form_type' => 'text', 'name' => 'video_controller_password', 'description' => 'RKM Server '.trans('messages.Password'), 'space' => 1],

                ['form_type' => 'text', 'name' => 'video_encoder', 'description' => 'Video Encoding Server '.trans('messages.Address'), 'options' => ['placeholder' => '172.20.0.12:1702'], 'space' => 1],
            ];
        }

        $c = [];
        if (Module::collections()->has('HfcCustomer')) {
            $c = [
                ['form_type' => 'text', 'name' => 'us_single_warning', 'description' => 'Upstream single warning threshhold', 'options' => ['placeholder' => '50']],
                ['form_type' => 'text', 'name' => 'us_single_critical', 'description' => 'Upstream single critical threshhold', 'options' => ['placeholder' => '55']],
                ['form_type' => 'text', 'name' => 'us_avg_warning', 'description' => 'Upstream average Warning Threshhold', 'options' => ['placeholder' => '45']],
                ['form_type' => 'text', 'name' => 'us_avg_critical', 'description' => 'Upstream average critical Threshhold', 'options' => ['placeholder' => '52'], 'space' => 1],
                ['form_type' => 'text', 'name' => 'online_absolute_minor', 'description' => 'Absolute Modem Offline Threshhold: Minor', 'options' => ['placeholder' => '5']],
                ['form_type' => 'text', 'name' => 'online_absolute_major', 'description' => 'Absolute Modem Offline Threshhold: Major', 'options' => ['placeholder' => '25']],
                ['form_type' => 'text', 'name' => 'online_absolute_critical', 'description' => 'Absolute Modem Offline Threshhold: Critical', 'options' => ['placeholder' => '100'], 'space' => 1],
                ['form_type' => 'text', 'name' => 'online_percentage_minor', 'description' => 'Percentage Modem Offline Threshhold: Minor', 'options' => ['placeholder' => '34']],
                ['form_type' => 'text', 'name' => 'online_percentage_major', 'description' => 'Percentage Modem Offline Threshhold: Major', 'options' => ['placeholder' => '51']],
                ['form_type' => 'text', 'name' => 'online_percentage_critical', 'description' => 'Percentage Modem Offline Threshhold: Critical', 'options' => ['placeholder' => '81']],
            ];
        }

        return array_merge($a, $b, $c);
    }

    /**
     * retrieve file if existent, this can be only used by authenticated and
     * authorized users (see corresponding Route::get in Http/routes.php)
     *
     * @author Ole Ernst
     *
     * @param string $type filetype, either kml or svg
     * @param string $filename name of the file
     * @return mixed
     */
    public function get_file($type, $filename)
    {
        $path = storage_path("app/data/hfcbase/$type/$filename");
        if (file_exists($path)) {
            return \Response::file($path);
        } else {
            return \App::abort(404);
        }
    }

    /**
     * KML Upload Array: Generate the KML file array
     *
     * @param Collection of NetElements
     * @return Collection KML files, like ['file', 'descr']
     *
     * @author Torsten Schmidt, Christian Schramm
     */
    public function kml_file_array($netelement)
    {
        return $netelement->filter(function ($tree) {
            return $tree->kml_file != '';
        })->map(function ($tree) {
            return [
                'file' => route('HfcBase.get_file', ['type' => 'kml_static', 'filename' => basename($tree->kml_file)]),
                'descr' => $tree->kml_file,
            ];
        });
    }
}
