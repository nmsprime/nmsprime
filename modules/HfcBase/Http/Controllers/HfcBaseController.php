<?php

namespace Modules\HfcBase\Http\Controllers;

use View;
use Module;
use Modules\HfcReq\Entities\NetElement;
use App\Http\Controllers\BaseController;
use Modules\HfcBase\Entities\IcingaObject;

class HfcBaseController extends BaseController
{
    public function index()
    {
        $title = 'Hfc Dashboard';

        $netelements = $this->get_impaired_netelements();
        $services = $this->get_impaired_services();

        return View::make('HfcBase::index', $this->compact_prep_view(compact('title', 'netelements', 'services')));
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

    /**
     * Return all impaired netelements in a table array
     *
     * @author Ole Ernst
     * @return array
     */
    public static function get_impaired_netelements()
    {
        $ret = [];

        if (! IcingaObject::db_exists()) {
            return $ret;
        }

        $elements = NetElement::where('id', '>', '2')
            ->where('netelementtype_id', '>', '2')
            ->with(['icingaobject', 'icingaobject.icingahoststatus'])
            ->get();

        foreach ($elements as $element) {
            $obj = $element->icingaobject;
            if (! isset($obj)) {
                continue;
            }

            $status = $obj->icingahoststatus;
            if (! isset($status) || $status->problem_has_been_acknowledged || ! $status->last_hard_state) {
                continue;
            }

            $link = link_to('https://'.\Request::server('HTTP_HOST').'/icingaweb2/monitoring/host/show?host='.$element->id, $element->name);
            $ret['clr'][] = 'danger';
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
    public static function get_impaired_services()
    {
        $ret = [];
        $clr = ['success', 'warning', 'danger', 'info'];

        if (! IcingaObject::db_exists()) {
            return $ret;
        }

        $objs = IcingaObject::join('icinga_servicestatus', 'object_id', '=', 'service_object_id')
            ->where('is_active', '=', '1')
            ->where('name2', '<>', 'ping4')
            ->where('last_hard_state', '<>', '0')
            ->where('problem_has_been_acknowledged', '<>', '1')
            ->orderByRaw("name2='clusters' desc")
            ->orderBy('last_time_ok', 'desc');

        foreach ($objs->get() as $service) {
            $tmp = NetElement::find($service->name1);

            $link = link_to('https://'.\Request::server('HTTP_HOST').'/icingaweb2/monitoring/service/show?host='.$service->name1.'&service='.$service->name2, $tmp ? $tmp->name : $service->name1);
            // add additional controlling link if available
            $id = explode('_', $service->name1)[0];
            if (is_numeric($id)) {
                $link .= '<br>'.link_to_route('NetElement.controlling_edit', '(Controlling)', [$id, 0, 0]);
            }

            $ret['clr'][] = $clr[$service->last_hard_state];
            $ret['row'][] = [$link, $service->name2, preg_replace('/[<>]/m', '', $service->output), $service->last_time_ok];
            $ret['perf'][] = self::deserializePerfdata($service->perfdata);
        }

        if ($ret) {
            $ret['hdr'] = ['Host', 'Service', 'Status', 'since'];
        }

        return $ret;
    }

    /**
     * Return formatted impaired performance data for a given perfdata string
     *
     * @author Ole Ernst, Christian Schramm
     * @param string $perf
     * @return array
     */
    private static function deserializePerfdata(string $perf): array
    {
        $ret = [];
        preg_match_all("/('.+?'|[^ ]+)=([^ ]+)/", $perf, $matches, PREG_SET_ORDER);

        foreach ($matches as $idx => $match) {
            $data = explode(';', rtrim($match[2], ';'));
            [$value, $warningThreshhold, $criticalThreshhold] = $data;
            $unifiedValue = preg_replace('/[^0-9.]/', '', $value); // remove unit of measurement, such as percent

            if (substr($value, -1) == '%' || (isset($data[3]) && isset($data[4]))) { // we are dealing with percentages
                $min = $data[3] ?? 0;
                $max = $data[4] ?? 100;
                $percentage = ($max - $min) ? (($unifiedValue - $min) / ($max - $min) * 100) : null;
                $percentageText = sprintf(' (%.1f%%)', $percentage);
            }

            if (isset($warningThreshhold) && isset($criticalThreshhold)) { // set the html color
                $htmlClass = self::getPerfDataHtmlClass($unifiedValue, $warningThreshhold, $criticalThreshhold);

                if ($htmlClass === 'success') { // don't show non-impaired perf data
                    unset($ret[$idx]);
                    continue;
                }
            }

            $ret[$idx]['val'] = $value;
            $ret[$idx]['text'] = $match[1].($percentageText ?? null);
            $ret[$idx]['cls'] = $htmlClass ?? null;
            $ret[$idx]['per'] = $percentage ?? null;
        }

        return $ret;
    }

    /**
     * Return performance data colour class according to given limits
     *
     * @author Ole Ernst, Christian Schramm
     * @param int $value
     * @param int $warningThreshhold
     * @param int $criticalThreshhold
     * @return string
     */
    private static function getPerfDataHtmlClass(int $value, int $warningThreshhold, int $criticalThreshhold): string
    {
        if ($criticalThreshhold > $warningThreshhold) { // i.e. for upstream power
            [$value, $warningThreshhold,$criticalThreshhold] =
                negate($value, $warningThreshhold, $criticalThreshhold);
        }

        if ($value > $warningThreshhold) {
            return 'success';
        }

        if ($value > $criticalThreshhold) {
            return 'warning';
        }

        return 'danger';
    }
}
