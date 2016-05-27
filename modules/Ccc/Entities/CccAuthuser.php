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

	// SQL connection
	// This is a security plus to let the CCC sql user only have read-only access to the required tables
	protected $connection = 'mysql-ccc';

	// The associated SQL table for this Model
	public $table = 'cccauthusers';


	public function contract()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Contract', 'contract_id');
	}

}

