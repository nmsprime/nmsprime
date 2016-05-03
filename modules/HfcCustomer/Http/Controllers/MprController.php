<?php namespace Modules\Hfccustomer\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Hfccustomer\Entities\MprGeopos;


class MprController extends \BaseModuleController {

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'text', 'name' => 'value', 'description' => 'Value (deprecated)'),
			array('form_type' => 'select', 'name' => 'tree_id', 'description' => 'Tree', 'value' => $model->html_list($model->trees(), 'name')),
			array('form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' =>
				array(1 => 'position rectangle', 2 => 'position polygon', 3 => 'nearest amp/node object', 4 => 'assosicated upstream interface', 5 => 'cluster (deprecated)')),
			array('form_type' => 'text', 'name' => 'prio', 'description' => 'Priority (lower runs first)'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description')
		);
	}


	/*
	 * MPR specific Store Function. Overwrites Base Controller store()
	 * This function handles the rectangle add in topography card
	 * shift key, draw rectangle, add modem positioning rule
	 *
	 * NOTE: param/return: see BaseController@store
	 */
	protected function store ($redirect = true)
	{
		$mpr_id = parent::store(false);

		if (\Input::all()['value'])
		{
			$pos = explode (';', \Input::all()['value']);

			// only add if we have 4 geopos for a valid rectangle
			if (count ($pos) == 4)
			{
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
			}
		}

		return \Redirect::route(static::get_route_name().'.edit', $mpr_id)->with('message', 'Created!');
	}

}