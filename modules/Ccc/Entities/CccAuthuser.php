<?php namespace Modules\Ccc\Entities;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;


/**
 * Model holding user data for CCC authentication
 *
 * NOTE: this is a simple (reduced) copy of @Patrick Reichels Authuser class
 */
class CccAuthuser extends \BaseModel implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;


	// The associated SQL table for this Model
	public $table = 'cccauthusers';

}

