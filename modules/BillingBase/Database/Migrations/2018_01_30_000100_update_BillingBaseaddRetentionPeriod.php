<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add retention period for CDRs
 *
 * @author Nino Ryschawy
 */
class UpdateBillingBaseaddRetentionPeriod extends BaseMigration
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
            $table->smallInteger('cdr_retention_period');               // Time CDRs have to be kept save in months
        });

        // Set default value
        \DB::table($this->tablename)->update(['cdr_retention_period' => 6]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn('cdr_retention_period');
        });
    }
}
