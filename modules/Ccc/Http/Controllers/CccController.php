<?php namespace Modules\Ccc\Http\Controllers;


class CccController extends \BaseModuleController {

	// Constructor
	public function show()
	{
		return \View::make('ccc::index');
	}

}