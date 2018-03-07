<?php

namespace Modules\BillingBase\Database\Seeders;

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\BillingBase\Entities\Product;


class ProductTableSeeder extends \BaseSeeder {

	public function run()
	{
		$faker = Faker::create();

		$voip_name = [1 => 'Voip Base', 2 => 'Voip Flat', 3 => 'Voip Flat reduced'];
		$cycles = [1 => 'Once', 2 => 'Monthly', 3 => 'Yearly'];
		$other_names = [1 => 'Antenna Dose Installation', 2 => 'Public IP', 3 => 'fixed cycle prod'];

		foreach(Product::getPossibleEnumValues('type') as $type)
		{
			foreach (range(1,3) as $index)
			{
				$name = '';
				$qos_id = $voip_sale_id = $voip_purchase_tariff_id = $maturity = $costcenter_id = $price = $email_count = 0;
				$tax = 1;
				$billing_cycle = 'Once';

				switch ($type)
				{
					case 'Internet':
						$name = 'Flat '.(2*pow(10, $index-1)).' Mbit/s';
						$price = 10 * $index;
						$qos_id = $index;
						$billing_cycle = 'Monthly';
						$bundled_with_voip = rand(0, 1);
						$email_count = rand(0,10);
						break;

					case 'Voip':
						$name = $voip_name[$index];
						$price = 5 * $index;
						$voip_sale_id = 1 ; 		// (int) (($index+3)/3);
						$billing_cycle = 'Monthly';
						$voip_purchase_tariff_id = 2;
						$bundled_with_voip = 0;
						break;

					case 'TV':
						$name = 'TV '.$faker->city;
						$price = rand(40, 80);
						$billing_cycle = 'Yearly';
						$tax = rand(0,1);
						$costcenter_id = rand(0,10) > 3 ? 0 : $index;
						$bundled_with_voip = 0;
						break;

					case 'Device':
						$name = 'Cable Modem '.$index.'.0';
						$price = 50 * $index;
						$bundled_with_voip = 0;
						break;

					case 'Credit':
						$name = $type.' '.$cycles[$index];
						$billing_cycle = $cycles[$index];
						$bundled_with_voip = 0;
						break;

					case 'Other':
						$name = $other_names[$index];
						$price = rand(2, 5);
						$maturity = $index == 3 ? 18 : 0;
						$bundled_with_voip = 0;
						break;
				}

				Product::create([
					'name' => $name,
					'type' => $type,
					'qos_id' => $qos_id,
					'voip_sales_tariff_id' => $voip_sale_id,
					'voip_purchase_tariff_id' => $voip_purchase_tariff_id,
					'billing_cycle' => $billing_cycle,
					'maturity' => $maturity,
					'costcenter_id' => $costcenter_id,
					'price' => $price,
					'tax' => $tax,
					'bundled_with_voip' => $bundled_with_voip,
					'email_count' => $email_count,
					]);
			}
		}
	}

}
