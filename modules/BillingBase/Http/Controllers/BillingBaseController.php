<?php namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class BillingBaseController extends \BaseModuleController {
	
	public function index()
	{
		return view('billingbase::index');
	}

	public function get_form_fields($model = null)
	{
	}
	
}