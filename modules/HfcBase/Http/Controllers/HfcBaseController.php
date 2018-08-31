<?php

namespace Modules\HfcBase\Http\Controllers;

use App\Http\Controllers\BaseController;

class HfcBaseController extends BaseController
{
    // The Html Link Target
    protected $html_target = '';

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
     * @param Collection|NetElement $trees
     * @return Collection KML files, like ['file', 'descr']
     *
     * @author Torsten Schmidt, Christian Schramm
     */
    public function kml_file_array($trees)
    {
        return $trees->filter(function ($tree) {
            return $tree->kml_file != '';
        })->map(function ($tree) {
            return [
                'file' => route('HfcBase.get_file', ['type' => 'kml_static', 'filename' => basename($tree->kml_file)]),
                'descr' => $tree->kml_file,
            ];
        });
    }
}
