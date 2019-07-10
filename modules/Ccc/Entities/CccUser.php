<?php

namespace Modules\Ccc\Entities;

use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * This is the Model, holding the User data for CCC authentication.
 * To gain access data the Middleware will check for Permissions.
 */
class CccUser extends \BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, HasRolesAndAbilities, Notifiable;

    public $table = 'cccauthuser';
    // SQL connection
    // This is a security plus to let the CCC sql user only have read-only access to the required tables
    protected $connection = 'mysql-ccc';

    protected $guard = 'ccc';

    public function contract()
    {
        $this->connection = 'mysql';
        $relation = $this->belongsTo('Modules\ProvBase\Entities\Contract', 'contract_id');
        $this->connection = 'mysql-ccc';

        return $relation;
    }

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

        if ($data) {
            parent::update($data['data']);

            return ['login_name' => $data['data']['login_name'], 'password' => $data['psw'], 'id' => $this->contract_id];
        }

        return [];
    }

    /**
     * Add new Function to create/store new CccUsers
     */
    public function store()
    {
        $data = $this->generate_user_data();

        if ($data) {
            $this->create($data['data']);

            return ['login_name' => $data['data']['login_name'], 'password' => $data['psw'], 'id' => $this->contract_id];
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

        if ($contract) {
            $psw = \Acme\php\Password::generate_password();

            $data = [
                'contract_id' => $this->contract_id,
                'login_name'  => $contract->number,
                'password' 	  => \Hash::make($psw),
                'first_name'  => $contract->firstname,
                'last_name'   => $contract->lastname,
                'email' 	  => $contract->email,
                // 'active' 	  => 1 // TODO: deactivate non active customers for login
                'active' 	  => $contract->check_validity('Now') ? 1 : 0,
            ];
        } else {
            Log::error('Contract for CccUser does not exist', [$this->id]);
        }

        return isset($psw) ? ['data' => $data, 'psw' => $psw] : [];
    }
}
