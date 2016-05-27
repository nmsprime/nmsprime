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


	/**
	 * Create/Update Customer Control Information
	 * Save the model to the database.
	 *
	 * @param  array  $options
	 * @return array with [login, password, contract id)] or bool if no contract relation
	 *
	 * @author Torsten Schmidt
	 */
	public function save(array $options = [])
	{
		$contract = $this->contract;

		if ($contract)
		{
			$psw = \Acme\php\Password::generate_password();
			$this->login_name = $contract->number;
			$this->password = \Hash::make($psw);
			$this->first_name = $contract->firstname;
			$this->last_name = $contract->lastname;
			$this->email = $contract->email;
			$this->active = 1; // TODO: deactivate non active customers for login
		}

		$ret = parent::save();

		return ($contract && $ret ? ['login' => $contract->number, 'password' => $psw, 'id' => $contract->id] : $ret);
	}

}

