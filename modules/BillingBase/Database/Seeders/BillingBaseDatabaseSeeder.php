<?php namespace Modules\Billingbase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BillingBaseDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		$this->call("CostCenterTableSeeder");
		$this->call("ProductTableSeeder");
		$this->call("SepaAccountTableSeeder");
		$this->call("CompanyTableSeeder");
		$this->call("SalesmanTableSeeder");				// half dependent on Contract Seeds - but not mandatory
		$this->call("ItemTableSeeder");					// dependent on Contract Seeds !!
	}

}