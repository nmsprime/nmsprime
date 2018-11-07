<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add Index to improve performance
 *
 * e.g. time to get 5k contracts in SettlementRunCommand reduces from 8 sec to 0,22 sec
 *
 * @author Nino Ryschawy
 */
class UpdateItemAddIndex extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->index('contract_id', 'by_contract_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropIndex('by_contract_id');
        });
    }
}
