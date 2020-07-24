<?php

namespace Modules\HfcBase\Http\Controllers;

use View;
use Module;
use App\Http\Controllers\BaseController;
use Modules\Dashboard\Http\Controllers\DashboardController;

class HfcBaseController extends BaseController
{
    /**
     * Compose HFC Base Dashboard
     *
     * @return Illuminate\View\View
     */
    public function index()
    {
        $title = 'Hfc Dashboard';

        // This is the most timeconsuming task
        $modem_statistics = DashboardController::get_modem_statistics();

        return View::make('HfcBase::index', $this->compact_prep_view(compact('title', 'modem_statistics')));
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

                ['form_type' => 'text', 'name' => 'video_encoder', 'description' => 'Video Encoding Server '.trans('messages.Address'), 'options' => ['placeholder' => '172.20.0.12:1702']],
            ];
        }

        return array_merge($a, $b);
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
