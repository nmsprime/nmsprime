<?php

namespace Modules\BillingBase\Database\Seeders;

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\BillingBase\Entities\SepaMandate;
use Modules\ProvBaseBase\Entities\Contract;

class SepaMandateTableSeeder extends \BaseSeeder {

	public function run()
	{
		$faker = Faker::create();

		$contracts = Contract::all();
		foreach($contracts as $c)
			$contract_ids[] = $c->id;

		$voip_name = [1 => 'Voip Base', 2 => 'Voip Flat', 3 => 'Voip Flat reduced'];
		$cycles = [1 => 'Once', 2 => 'Montly', 3 => 'Yearly'];
		$other_names = [1 => 'Antenna Dose Installation', 2 => 'Public IP', 3 => 'fixed cycle prod'];

		$ibans = ['DE89 3704 0044 0532 0130 00', 'DE12500105170648489890'];
		$bics  = ['WELADED1STB', 'DRESDEFF870', 'CSDBDE71XXX'];

		// choose randomly 6 contracts
		foreach (range(1,6) as $index)
		{
			$add = rand(0, 10)) > 7 ? 0 : 1;
			$index = $index % count($contract_ids);
			$contract_id = $contract_ids[$index + $add] > $contract_id ? $contract_ids[$index + $add] : $contract_ids[$index + $add + 1];
			$date = date('Y-m-d', strtotime('-'.rand(5, 1000).' days'));

			SepaMandate::create([
				'contract_id' => $contract_id,
				'reference' => '002-'-$contract_id,
				'signature_date' => $date,
				'sepa_holder' => $contracts->find($contract_id)->firstname.' '.$contracts->find($contract_id)->lastname,
				'sepa_iban'=> $ibans[rand(0,1)],
				'sepa_bic' => $bics[rand(0,2)],
				'sepa_institute' => '',
				'sepa_valid_from' => $date,
				'sepa_valid_to' => null,
				'recurring' => 0,
				'state' => '',
				]);
			}
		}


	}

}