<?php 

namespace Modules\Billingbase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BillingbaseDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		$this->call("Modules\BillingBase\Database\Seeders\CostCenterTableSeeder");
		$this->call("Modules\BillingBase\Database\Seeders\ProductTableSeeder");
		$this->call("Modules\BillingBase\Database\Seeders\SepaAccountTableSeeder");
		$this->call("Modules\BillingBase\Database\Seeders\CompanyTableSeeder");
		$this->call("Modules\BillingBase\Database\Seeders\SalesmanTableSeeder");				// half dependent on Contract Seeds - but not mandatory
		$this->call("Modules\BillingBase\Database\Seeders\SepaMandateTableSeeder");
		$this->call("Modules\BillingBase\Database\Seeders\ItemTableSeeder");					// dependent on Contract and Product Seeds !!
	}

}