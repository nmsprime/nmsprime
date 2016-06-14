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
	// public function save(array $options = [])
	// {
	// 	$contract = $this->contract;

	// 	if ($contract)
	// 	{
	// 		$psw = \Acme\php\Password::generate_password();
	// 		$this->login_name = $contract->number;
	// 		$this->password = \Hash::make($psw);
	// 		$this->first_name = $contract->firstname;
	// 		$this->last_name = $contract->lastname;
	// 		$this->email = $contract->email;
	// 		$this->active = 1; // TODO: deactivate non active customers for login
	// 	}

	// 	$ret = parent::save();

	// 	return ($contract && $ret ? ['login' => $contract->number, 'password' => $psw, 'id' => $contract->id] : $ret);
	// }


	/**
	 * Overwrite Eloquent Function
	 * NOTE: We can not overwrite save()-function here as it is called on logout what in consequence changes the password 
	 * of the user and a new login isnt possible anymore
	 *
	 * @return array  	login data
	 */
	public function update(array $attributes = [], array $options = [])
	{
		$data = $this->generate_user_data();

		if ($data)
		{
			parent::update($data['data']);
			return ['login_name' => $data['data']['login_name'] , 'password' => $data['psw'], 'id' => $this->contract_id];
		}

		return [];
	}


	/**
	 * Add new Function to create/store new CccUsers
	 */
	public function store()
	{
		$data = $this->generate_user_data();

		if ($data)
		{
			$this->create($data['data']);
			return ['login_name' => $data['data']['login_name'] , 'password' => $data['psw'], 'id' => $this->contract_id];
		}

		return [];
	}


	/**
	 * Generate Customer User Data for Creating/Updating a User related to a contract
	 *
	 * @return array 	user data for sql db
	 *
	 * @author Nino Ryschawy
	 */
	private function generate_user_data()
	{
		$contract = $this->contract;

		if ($contract)
		{
			$psw = \Acme\php\Password::generate_password();

			$data = array(
				'contract_id' => $this->contract_id,
				'login_name'  => $contract->number,
				'password' 	  => \Hash::make($psw),
				'first_name'  => $contract->firstname,
				'last_name'   => $contract->lastname,
				'email' 	  => $contract->email,
				// 'active' 	  => 1 // TODO: deactivate non active customers for login
				'active' 	  => $contract->check_validity('now') ? 1 : 0,
			);
		}
		else
			Log::error('Contract for CccAuthuser does not exist', [$this->id]);

		return isset($psw) ? ['data' => $data, 'psw' => $psw] : [];

	}

}

