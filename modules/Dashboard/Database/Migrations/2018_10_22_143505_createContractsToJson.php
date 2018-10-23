<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Dashboard\Entities\BillingAnalysis;

class CreateContractsToJson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        BillingAnalysis::saveContractsToJson();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // rollback function isn't given if file exists
        \Storage::delete('contracts.json');
    }
}
