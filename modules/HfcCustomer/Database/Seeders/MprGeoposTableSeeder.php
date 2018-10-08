<?php

namespace Modules\HfcCustomer\Database\Seeders;

use Faker\Factory as Faker;
use Modules\HfcCustomer\Entities\Mpr;
use Modules\HfcCustomer\Entities\MprGeopos;

class MprGeoposTableSeeder extends \BaseSeeder
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
            $mpr = Mpr::all()->random(1)->id;

            $x = 13 + $faker->longitude() / 10;
            $y = 50 + $faker->latitude() / 10;

            MprGeopos::create([
                'name' => $faker->colorName(),
                'mpr_id' => $mpr,
                'x' => $x,
                'y' => $y,
            ]);

            MprGeopos::create([
                'name' => $faker->colorName(),
                'mpr_id' => $mpr,
                'x' => $x + rand(-10000, 10000) / rand(100, 1000),
                'y' => $y + rand(-10000, 10000) / rand(100, 1000),
            ]);
        }
    }
}
