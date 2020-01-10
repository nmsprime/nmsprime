<?php

namespace Modules\BillingBase\Database\Seeders;

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\BillingBase\Entities\Company;

class CompanyTableSeeder extends \BaseSeeder
{
    public function run()
    {
        $faker = Faker::create();

        $enterprises = [1 => 'AG', 2 => 'GmbH'];

        foreach (range(1, 2) as $index) {
            Company::create([
                'name' => 'MonsterNet '.$enterprises[$index],
                'street' => $faker->streetName,
                'zip' => $faker->postcode,
                'city' => $faker->city,
                'phone' => $faker->phoneNumber,
                'fax' => $faker->phoneNumber,
                'web' => $faker->domainName,
                'mail' => $faker->email,
                'management' => 'Dipl. Ing.'.$faker->firstname.' '.$faker->lastname.', '.$faker->firstname.' '.$faker->lastname,
                'tax_id_nr' => 'DE123456789',
                'tax_nr' => '123/456/78910',
                'logo' => 'logo.pdf',
            ]);
        }
    }
}
