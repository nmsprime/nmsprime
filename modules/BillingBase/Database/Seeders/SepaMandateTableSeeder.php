<?php

namespace Modules\BillingBase\Database\Seeders;

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\SepaMandate;

class SepaMandateTableSeeder extends \BaseSeeder
{
    public function run()
    {
        $faker = Faker::create();

        $contracts = Contract::all();
        foreach ($contracts as $c) {
            $contract_ids[] = $c->id;
        }

        $ibans = ['DE89 3704 0044 0532 0130 00', 'DE12500105170648489890'];
        $bics = ['WELADED1STB', 'DRESDEFF870', 'CSDBDE71XXX'];

        $contract_id = 0;

        // choose randomly 6 contracts
        foreach (range(1, 6) as $index) {
            $add = rand(0, 10) > 7 ? 0 : 1;
            $index = $index % count($contract_ids);
            $contract_id = $contract_ids[$index + $add] > $contract_id ? $contract_ids[$index + $add] : $contract_ids[$index + $add + 1];
            $date = date('Y-m-d', strtotime('-'.rand(5, 1000).' days'));

            SepaMandate::create([
                'contract_id' => $contract_id,
                'reference' => '002-'.$contract_id,
                'signature_date' => $date,
                'holder' => $contracts->find($contract_id)->firstname.' '.$contracts->find($contract_id)->lastname,
                'iban'=> $ibans[rand(0, 1)],
                'bic' => $bics[rand(0, 2)],
                'institute' => '',
                'valid_from' => $date,
                'valid_to' => null,
                'disable' => 0,
                'state' => '',
            ]);
        }
    }
}
