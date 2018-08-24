<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add flag for fluid start and end dates of tariffs
 *
 * @author Nino Ryschawy
 */
class UpdateBillingBaseaddAGsCheckbox extends BaseMigration
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
            $table->boolean('show_ags')->default(0); 			// Show Antennengemeinschaften select field on contract page
        });

        Schema::table('contract', function (Blueprint $table) {
            $table->integer('contact'); 			// Show Antennengemeinschaften select field on contract page
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
            $table->dropColumn('show_ags');
        });

        Schema::table('contract', function (Blueprint $table) {
            $table->dropColumn('contact'); 			// Show Antennengemeinschaften select field on contract page
        });
    }
}
