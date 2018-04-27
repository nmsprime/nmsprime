<?php namespace Modules\Ccc\Http\Controllers;

use App\Http\Controllers\AuthController as Auth;
use Pingpong\Modules\Routing\Controller;
use Modules\Ccc\Entities\Ccc;

/**
 * Extends Basic AuthController from @Patrick Reichel
 *
 * NOTE: this class inherit all the show(), login(), logout() stuff
 *
 * @author Torsten Schmidt
 */
class AuthController extends Auth {

	// Constructor
	public function __construct()
	{
		$conf = Ccc::first();

		$this->prefix = 'customer'; // url prefix
		$this->headline1 = $conf->headline1; //'ERZNET';
		$this->headline2 = $conf->headline2; //'Customer Control Center';
		$this->login_page = 'home'; // continue at ccc/home after successful login
		$this->guard = 'ccc'; // use guard. See: config/auth.php
		$this->image = 'main-pic-3.png';
	}

}
