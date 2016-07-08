<?php namespace Modules\Hfccustomer\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class HfcCustomerController extends Controller {

	public function index()
	{
		return View::make('hfccustomer::index');
	}

	/**
	 * retrieve file if existent, this can be only used by authenticated and
	 * authorized users (see corresponding Route::get in Http/routes.php)
	 *
	 * @author Ole Ernst
	 *
	 * @param string $filename name of the file
	 * @return mixed
	 */
	public function get_file($filename)
	{
		$path = storage_path("app/data/hfccustomer/kml/$filename");
		if (file_exists($path))
			return \Response::file($path);
		else
			return \App::abort(404);
	}

}
