<?php namespace Modules\Ccc\Http\Controllers;


class CccController extends \BaseController {

	// Constructor
	public function show()
	{
		return \View::make('ccc::index');
	}

}