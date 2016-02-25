<?php namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class BillingBaseController extends Controller {
	
	public function index()
	{
		return view('billingbase::index');
	}
	
}