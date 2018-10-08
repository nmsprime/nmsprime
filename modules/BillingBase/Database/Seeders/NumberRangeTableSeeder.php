<?php

namespace Modules\BillingBase\Database\Seeders;

// // Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\BillingBase\Entities\NumberRange;

class NumberRangeTableSeeder extends \BaseSeeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, self::$max_seed_l2) as $index) {
            for ($i = 1; $i <= 2; $i++) {
                NumberRange::create([
                    'name' => 'number_range_'.$faker->colorName(),
                    'start' => $i * 1000,
                    'end' => $i * 1000 + 1000,
                    'prefix' => "00$index-",
                    'costcenter_id' => $index,
                ]);
            }
        }
    }
}
