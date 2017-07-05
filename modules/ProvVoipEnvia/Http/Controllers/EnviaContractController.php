<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\EnviaContract;
use Modules\ProvVoip\Entities\PhonenumberManagement;
use Modules\ProvVoip\Entities\Phonenumber;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Contract;


class EnviaContractController extends \BaseController {

	protected $index_create_allowed = false;
	protected $index_delete_allowed = false;

	/* public function index() */
	/* { */
	/* 	return view('provvoipenvia::index'); */
	/* } */

}
