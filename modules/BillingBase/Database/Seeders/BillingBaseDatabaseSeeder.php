<?php

namespace Modules\Billingbase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BillingBaseDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call("Modules\BillingBase\Database\Seeders\CostCenterTableSeeder");				// dependent on Contract Seeds - but not mandatory ? (only sql update)
        $this->call("Modules\BillingBase\Database\Seeders\NumberRangeTableSeeder");				// dependent on Contract Seeds - but not mandatory ? (only sql update)
        $this->call("Modules\BillingBase\Database\Seeders\ProductTableSeeder");
        $this->call("Modules\BillingBase\Database\Seeders\SepaAccountTableSeeder");
        $this->call("Modules\BillingBase\Database\Seeders\CompanyTableSeeder");
        $this->call("Modules\BillingBase\Database\Seeders\SalesmanTableSeeder");				// dependent on Contract Seeds - but not mandatory ? (only sql update)
        $this->call("Modules\BillingBase\Database\Seeders\SepaMandateTableSeeder");
        $this->call("Modules\BillingBase\Database\Seeders\ItemTableSeeder");					// dependent on Contract and Product Seeds !!
    }
}
