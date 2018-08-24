<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add flag for fluid start and end dates of tariffs
 *
 * @author Nino Ryschawy
 */
class UpdateBillingBaseaddFluidDatesFlag extends BaseMigration
{
    protected $tablename = 'billingbase';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            // if 1: valid_from_fixed and valid_to_fixed in Item is shown for tariffs, otherwise they are hidden and set to 1 by default
            $table->boolean('fluid_valid_dates');
        });

        Schema::table('item', function (Blueprint $table) {
            $table->boolean('valid_from_fixed')->default(1)->change();
            $table->boolean('valid_to_fixed')->default(1)->change();
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
            $table->dropColumn('fluid_valid_dates');
        });

        // Dont revert default values as this assumption is already made by all running systems
    }
}
