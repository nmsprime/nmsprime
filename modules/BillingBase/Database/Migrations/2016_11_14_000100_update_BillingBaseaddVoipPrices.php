<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add time offset of CDRs to Invoice for SettlementRun
 *
 * @author Nino Ryschawy
 */
class UpdateBillingBaseaddVoipPrices extends BaseMigration
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
            $table->float('voip_extracharge_default');               // additional mark-on
            $table->float('voip_extracharge_mobile_national');       // additional mark-on
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
            $table->dropColumn('voip_extracharge_default');
            $table->dropColumn('voip_extracharge_mobile_national');
        });
    }
}
