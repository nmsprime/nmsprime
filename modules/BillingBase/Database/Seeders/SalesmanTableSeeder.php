<?php

namespace Modules\BillingBase\Database\Seeders;

// // Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\BillingBase\Entities\Salesman;

class SalesmanTableSeeder extends \BaseSeeder
{
    public function run()
    {
        $faker = Faker::create();

        // foreach (range(1,2) as $index)
        Salesman::create([
            'firstname' => 'Klaus',
            'lastname' => 'Vertreter',
            'commission' => 10,
            'products' => 'Internet, Voip, Device',
        ]);

        $sm_id = Salesman::orderBy('id', 'desc')->select('id')->first()->id;

        \DB::update('Update contract set salesman_id='.$sm_id.' where mod(id, 5)=3');
    }
}
