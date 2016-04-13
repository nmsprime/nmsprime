<?php namespace Modules\Hfccustomer\Database\Seeders;

use Faker\Factory as Faker;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Modules\HfcCustomer\Entities\Mpr;
use Modules\HfcBase\Entities\Tree;


class MprTableSeeder extends \BaseSeeder {

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
			Mpr::create([
				'name' => 'Rule'.$faker->colorName(),
				'type' => 1, // pos rectangle
				'tree_id' => Tree::all()->random(1)->id,
			]);
		}
	}

}