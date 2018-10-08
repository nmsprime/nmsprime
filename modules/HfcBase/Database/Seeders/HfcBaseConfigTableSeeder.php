<?php

namespace Modules\HfcBase\Database\Seeders;

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Modules\HfcBase\Entities\HfcBase;

class HfcBaseConfigTableSeeder extends \BaseSeeder
{
    public function run()
    {
        $faker = Faker::create();
        HfcBase::create([
            'ro_community' => 'public',
            'rw_community' => 'private',
        ]);
    }
}
