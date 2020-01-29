<?php

namespace Modules\HfcBase\Http\Controllers;

use View;
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
        return [
            ['form_type' => 'text', 'name' => 'ro_community', 'description' => 'SNMP Read Only Community'],
            ['form_type' => 'text', 'name' => 'rw_community', 'description' => 'SNMP Read Write Community'],
        ];
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
            if (is_numeric($service->name1)) {
                $link .= '<br>'.link_to_route('NetElement.controlling_edit', '(Controlling)', [$service->name1, 0, 0]);
            }

            $ret['clr'][] = $clr[$service->last_hard_state];
            $ret['row'][] = [$link, $service->name2, preg_replace('/[<>]/m', '', $service->output), $service->last_time_ok];
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
}
