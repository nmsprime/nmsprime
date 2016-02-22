<?php namespace Modules\HfcCustomer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class HfcCustomerTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		$this->call("Modules\HfcCustomer\Database\Seeders\MprTableSeeder");
		$this->call("Modules\HfcCustomer\Database\Seeders\MprGeoposTableSeeder");
	}

}

