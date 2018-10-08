<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Rename Column to properly adapt column name to general naming conventions
 *
 * @author Nino Ryschawy
 */
class UpdateCostCenterTableBugfix extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'costcenter';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->renameColumn('sepa_account_id', 'sepaaccount_id');
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
            $table->renameColumn('sepaaccount_id', 'sepa_account_id');
        });
    }
}
