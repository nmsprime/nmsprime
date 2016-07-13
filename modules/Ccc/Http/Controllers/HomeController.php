<?php 
namespace Modules\Ccc\Http\Controllers;

use Modules\Ccc\Entities\Ccc;
use Modules\ProvBase\Entities\Contract;

class HomeController extends \BaseController {

	private $rel_dir_path_invoices = 'data/billingbase/invoice/';


	public function show()
	{
		$contract_id = \Auth::guard('ccc')->user()['contract_id'];
		$invoices 	 = \File::allFiles(storage_path('app/'.$this->rel_dir_path_invoices.$contract_id));

		// TODO: take from contract->country_id when it has usable values
		\App::setLocale('de');

		return \View::make('ccc::index', compact('invoices', 'contract_id'));
	}


	public function download($contract_id, $filename)
	{
		$dir = storage_path('app/'.$this->rel_dir_path_invoices.$contract_id.'/');

		return response()->download($dir.$filename);
	}

}