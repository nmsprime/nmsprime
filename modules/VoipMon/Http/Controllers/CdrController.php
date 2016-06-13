<?php namespace Modules\Voipmon\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class Cdr extends Controller {
	
	public function index()
	{
		return view('voipmon::index');
	}
	
}