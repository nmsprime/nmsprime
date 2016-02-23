<?php namespace Modules\Hfccustomer\Database\Seeders;

use Faker\Factory as Faker;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Modules\HfcCustomer\Entities\Mpr;
use Modules\HfcCustomer\Entities\MprGeopos;

class MprGeoposTableSeeder extends \BaseSeeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$faker = Faker::create();

		foreach(range(1, $this->max_seed) as $index)
		{
			$x = 13 + $faker->longitude() / 10;
			$y = 50 + $faker->latitude() / 10;

			MprGeopos::create([
				'name' => $faker->colorName(),
				'mpr_id' => Mpr::all()->random(1)->id,
				'x' => $x,
				'y' => $y,
			]);
		}
	}

}