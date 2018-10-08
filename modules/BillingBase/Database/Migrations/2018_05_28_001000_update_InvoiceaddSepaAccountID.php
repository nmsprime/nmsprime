<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add flag for fluid start and end dates of tariffs
 *
 * @author Nino Ryschawy
 */
class UpdateInvoiceaddSepaAccountID extends BaseMigration
{
    protected $tablename = 'invoice';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('sepaaccount_id')->after('settlementrun_id'); 		// for build of invoices pdf for each SEPA account
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
            $table->dropColumn('sepaaccount_id');
        });
    }
}
