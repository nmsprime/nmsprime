<?php namespace Modules\Ccc\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class CccController extends Controller {

	public function index()
	{
		return view('ccc::index');
	}

}