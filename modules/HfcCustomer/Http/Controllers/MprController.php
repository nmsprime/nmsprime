<?php

namespace Modules\HfcCustomer\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Modules\HfcCustomer\Entities\Mpr;
use Modules\HfcReq\Entities\NetElement;
use Nwidart\Modules\Routing\Controller;
use Modules\HfcCustomer\Entities\MprGeopos;
use App\Http\Controllers\BaseViewController;

class MprController extends \BaseController
{
    protected $index_create_allowed = false;

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        $empty_field = isset($model->id);
        $netelems = NetElement::join('netelementtype as nt', 'nt.id', '=', 'netelementtype_id')
                ->select(['netelement.id as id', 'netelement.name as name', 'nt.name as ntname'])
                ->get();

        $types = BaseviewController::translateArray([
            1 => 'position rectangle',
            2 => 'position polygon',
            3 => 'nearest amp/node object',
            4 => 'assosicated upstream interface',
            5 => 'cluster (deprecated)',
        ]);

        // label has to be the same like column in sql table
        return [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
            ['form_type' => 'text', 'name' => 'value', 'description' => 'Value (deprecated)', 'options' => ['readonly']],
            ['form_type' => 'select', 'name' => 'netelement_id', 'description' => 'NetElement', 'hidden' => '0', 'value' => $model->html_list($netelems, ['ntname', 'name'], $empty_field, ': ')],
            ['form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => $types, 'options' => ['translate' => true]],
            ['form_type' => 'text', 'name' => 'prio', 'description' => 'Priority', 'help' => "1) lower priority values are runs first\n2) later runs will overwrite former runs\ni.e. highest priority value will take precedence"],
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];
    }

    /*
     * MPR specific Store Function. Overwrites Base Controller store()
     * This function handles the rectangle add in topography card
     * shift key, draw rectangle, add modem positioning rule
     *
     * NOTE: param/return: see BaseController@store
     */
    public function store($redirect = true)
    {
        $mpr_id = parent::store(false);

        // parent::store redirected us -> escalate to upper layer
        if ($mpr_id instanceof RedirectResponse) {
            return $mpr_id;
        }

        if (\Request::has('value')) {
            $pos = explode(';', \Request::get('value'));

            // only add if we have 4 geopos for a valid rectangle
            if (count($pos) == 4) {
                // First Point (not ordered x/y)
                MprGeopos::create([
                    'name' => 'P1',
                    'mpr_id' => $mpr_id,
                    'x' => $pos[0],
                    'y' => $pos[2],
                ]);

                // Second Point (not ordered x/y)
                MprGeopos::create([
                    'name' => 'P2',
                    'mpr_id' => $mpr_id,
                    'x' => $pos[1],
                    'y' => $pos[3],
                ]);
            } elseif (count($pos) > 4 && ! (count($pos) % 2)) {
                for ($i = 0; $i < count($pos) / 2; $i++) {
                    MprGeopos::create(['name' => 'P'.$i, 'mpr_id' => $mpr_id, 'x' => $pos[2 * $i], 'y' => $pos[2 * $i + 1]]);
                }
            }
        }

        \Queue::push(new \Modules\HfcCustomer\Console\MpsCommand);

        return \Redirect::route(\NamespaceController::get_route_name().'.edit', $mpr_id)->with('message', 'Created!');
    }

    /**
     * An MPR-polygon was modified using openlayers, so updates its corresponding
     * mprgeoposes. This involves deleting superfluous mprgeoposes, updating them
     * and creating new ones if needed.
     *
     * @param id: MPR id
     * @param gp_new: new mprgeoposes separated by semicolons (string)
     * @return: redirect back
     * @author: Ole Ernst
     */
    public function update_geopos($id, $gp_new)
    {
        $gp_new = explode(';', $gp_new);
        // an odd number means a coordinate is incomplete
        if (count($gp_new) % 2) {
            return back();
        }

        // delete superfluous mprgeopos
        foreach (Mpr::find($id)->mprgeopos->slice(count($gp_new) / 2) as $gp_del) {
            $gp_del->observer_enabled = false;
            $gp_del->delete();
        }

        // update mprgeopos
        foreach (Mpr::find($id)->mprgeopos as $gp) {
            $gp->x = array_shift($gp_new);
            $gp->y = array_shift($gp_new);
            $gp->observer_enabled = false;
            $gp->save();
        }

        // add new mprgeopos
        while (count($gp_new)) {
            MprGeopos::create(['mpr_id' => $id, 'x' => array_shift($gp_new), 'y' => array_shift($gp_new)]);
        }

        \Queue::push(new \Modules\HfcCustomer\Console\MpsCommand);

        return back();
    }
}
