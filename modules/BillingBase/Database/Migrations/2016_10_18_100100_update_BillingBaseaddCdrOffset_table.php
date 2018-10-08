<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add time offset of CDRs to Invoice for SettlementRun
 *
 * @author Nino Ryschawy
 */
class UpdateBillingBaseaddCdrOffsetTable extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'billingbase';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->tinyInteger('cdr_offset');
        });

        // Set to 1 by default
        DB::table($this->tablename)->update(['cdr_offset' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn('cdr_offset');
        });
    }
}
