<?php namespace Modules\Ccc\Http\Controllers;

use App\Http\Controllers\AuthController as Auth;
use Pingpong\Modules\Routing\Controller;

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
		$this->prefix = 'customer'; // url prefix
		$this->headline1 = 'ERZNET';
		$this->headline2 = 'Customer Control Center';
		$this->login_page = 'home'; // continue at ccc/home after successful login
		$this->guard = 'ccc'; // use guard. See: config/auth.php
		$this->image = 'main-pic-3.png';
	}

}