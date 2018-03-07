<?php

namespace Modules\ProvBase\Database\Seeders;

use Modules\ProvBase\Entities\Endpoint;

class EndpointTableSeeder extends \BaseSeeder {

	public function run()
	{
		foreach(range(1, self::$max_seed_l2) as $index)
		{
			Endpoint::create(static::get_fake_data('seed'));
		}
	}


	/**
	 * Returns an array with faked endpoint data; used e.g. in seeding and testing
	 *
	 * @param $topic Context the method is used in (seed|test)
	 *
	 * @author Patrick Reichel
	 */
	public static function get_fake_data($topic, $contract=null) {

		$faker =& \NmsFaker::getInstance();

		$ret = [
			'mac' => $faker->macAddress(),
			'description' => $faker->realText(200),
			'hostname' => "$faker->domainWord.$faker->domainName",
		];

		return $ret;
	}
}
