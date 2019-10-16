<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add flag for setting if all item start dates shall be adapted to contracts start date when latter is changed
 *
 * @author Nino Ryschawy
 */
class UpdateBillingBaseAddAdaptItemFlag extends BaseMigration
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
            $table->boolean('adapt_item_start')->default(0);            // Show Antennengemeinschaften select field on contract page
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
            $table->dropColumn('adapt_item_start');
        });
    }
}
