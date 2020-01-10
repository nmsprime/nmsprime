<?php

namespace Modules\BillingBase\Database\Seeders;

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\BillingBase\Entities\SepaAccount;

class SepaAccountTableSeeder extends \BaseSeeder
{
    public function run()
    {
        $faker = Faker::create();

        $enterprises = [1 => 'AG', 2 => 'GmbH'];

        foreach (range(1, 2) as $index) {
            SepaAccount::create([
                'name' => 'MonsterNet '.$enterprises[$index],
                'holder' => $faker->firstname,
                'creditorid' => 'LU27ZZZ0000000000123456789',
                'iban' => 'AD1200012030200359100100',
                'bic' => 'WELADED1STB',
                'Institute' => 'Sparkasse',
                'company_id' => 1,
                'invoice_headline' => 'Rechnung',
                'invoice_text_sepa' => 'Der Rechnungsbetrag wird am {rcd} von folgendem Konto abgebucht:',
                'invoice_text_sepa_negativ' => 'Der Rechnungsbetrag wird dem folgenden Konto gutgeschrieben:',
                'invoice_text' => 'Bitte überweisen Sie den Rechnungsbetrag unter folgendem Verwendungszweck bis zum {rcd} auf das angegebene Konto:',
                'invoice_text_negativ' => 'Bitte informieren Sie uns über das Konto auf das die Gutschrift erfolgen soll.',
                'template_invoice' => 'erz.tex',
                'template_cdr' => 'cdr.tex',
            ]);
        }
    }
}
