<?php


// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class AuthuserTableSeeder extends \BaseSeeder {

	public function run()
	{

		DB::table('authusers')->insert(array(
			'id' => 1000,
			'first_name' => 'modem',
			'last_name' => 'reader',
			'login_name' => 'mr',
			'password' => Hash::make('123'),
			'description' => 'Testuser: Only allowed to read modems; password is “123”',
		));

		DB::table('authrole')->insert(array(
			'id' => 1000,
			'name' => 'modemread',
			'type' => 'role',
		));

		DB::table('authuser_role')->insert(array(
			'id' => 1000,
			'user_id' => 1000,
			'role_id' => 1000,
		));

		$model_modem_id = DB::table('authcores')
			->select('id')
			->where('name', 'LIKE', 'Modules\\\\ProvBase\\\\Entities\\\\Modem')
			->first();
		DB::table('authrole_core')->insert(array(
			'id' => 1000,
			'role_id' => 1000,
			'core_id' => intval($model_modem_id->id),
			'view' => 1,
		));

	}
}
