<?php

namespace Modules\BillingBase\Database\Seeders;

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\BillingBase\Entities\CostCenter;

class CostCenterTableSeeder extends \BaseSeeder
{
    public function run()
    {
        $faker = Faker::create();

        $voip_name = [1 => 'Voip Base', 2 => 'Voip Flat', 3 => 'Voip Flat reduced'];
        $cycles = [1 => 'Once', 2 => 'Montly', 3 => 'Yearly'];
        $other_names = [1 => 'Antenna Dose Installation', 2 => 'Public IP', 3 => 'fixed cycle prod'];

        foreach (range(1, self::$max_seed_l2) as $index) {
            $name = 'CC '.$index;
            $sepa_account_id = (int) ($index + 2 / 3);
            $billing_month = rand(3, 10);

            CostCenter::create([
                'name' => $name,
                'sepaaccount_id' => $sepa_account_id,
                'billing_month' => $billing_month,
            ]);
        }

        $cc_id = CostCenter::select('id')->get()->first()->id;

        \DB::update('Update contract set costcenter_id='.$cc_id);
    }
}
