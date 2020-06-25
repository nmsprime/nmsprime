<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add flag for fluid start and end dates of tariffs
 *
 * @author Nino Ryschawy
 */
class UpdateInvoiceaddChargeGross extends BaseMigration
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
            $table->decimal('charge_gross')->after('charge')->nullable();
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
            $table->dropColumn('charge_gross');
        });
    }
}
