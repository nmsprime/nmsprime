<?php

namespace Modules\BillingBase\Database\Seeders;

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\BillingBase\Entities\Item;
use Modules\BillingBase\Entities\Product;
use Modules\ProvBase\Entities\Contract;


class ItemTableSeeder extends \BaseSeeder {

	public function run()
	{
		$faker = Faker::create();

		$prods = Product::all();

		$tariffs = $prods->whereIn('type', ['Internet', 'Voip', 'TV']);
		foreach ($tariffs as $prod)
			$tariff_ids[] = $prod->id;

		$others = $prods->whereIn('type', ['Device', 'Credit', 'Other']);
		foreach ($others as $prod)
			$other_ids[] = $prod->id;

		foreach(Contract::select('id')->get()->all() as $c)
			$contract_ids[] = $c->id;

		$contract_id = $product_id = $costcenter_id = $credit_amount = $payed_month = 0;
		$count = 1;

		// 10 contracts
		foreach (range(0,9) as $index)
		{
			$k = $index % count($contract_ids);
			$contract_id = $contract_ids[$k];

			foreach (range(1,2) as $i)
			{
				// Add 2 tariffs
				$product_id = $tariff_ids[rand(0, count($tariff_ids) - 1)];
				$valid_from = date('Y-m-d', strtotime('-'.rand(1,20).' month'));
				$valid_to 	= rand(0,10) > 7 ? null : date('Y-m-d', strtotime('+'.rand(1,5).' month'));

				Item::create([
					'contract_id' 	=> $contract_id,
					'product_id' 	=> $product_id,
					'count' 		=> $count,
					'valid_from' 	=> $valid_from,
					'valid_from_fixed' => 1,
					'valid_to' 		=> $valid_to,
					'credit_amount' => $credit_amount,
					'costcenter_id' => $costcenter_id,
					'payed_month' 	=> $payed_month,
					]);


				// Add 2 Other Products
				$credit_amount  = 0;
				$valid_from 	= $i == 2 ? null : date('Y-m-d', strtotime('first day of last month'));
				$valid_to 		= $i == 2 ? null : date('Y-m-d', strtotime('+2 month'));
				$product_id 	= $other_ids[rand(0, count($other_ids) - 1)];

				if ($prods->find($product_id)->type == 'Credit')
					$credit_amount = 10 * $index;

				Item::create([
					'contract_id' 	=> $contract_id,
					'product_id' 	=> $product_id,
					'count' 		=> $count,
					'valid_from' 	=> $valid_from,
					'valid_to' 		=> $valid_to,
					'credit_amount' => $credit_amount,
					'costcenter_id' => $costcenter_id,
					'payed_month' 	=> $payed_month,
					]);
			}

		}

	}

}
