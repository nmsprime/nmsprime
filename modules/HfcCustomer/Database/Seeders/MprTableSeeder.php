<?php

namespace Modules\HfcCustomer\Database\Seeders;

use Faker\Factory as Faker;
use Modules\HfcCustomer\Entities\Mpr;
use Modules\HfcReq\Entities\NetElement;

class MprTableSeeder extends \BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, self::$max_seed) as $index) {
            Mpr::create([
                'name' => 'Rule'.$faker->colorName(),
                'type' => 1, // pos rectangle
                'netelement_id' => NetElement::all()->random(1)->id,
            ]);
        }
    }
}
