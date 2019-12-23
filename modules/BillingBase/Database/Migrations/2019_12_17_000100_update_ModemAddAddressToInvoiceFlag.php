<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateModemAddAddressToInvoiceFlag extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('modem', function (Blueprint $table) {
            $table->boolean('address_to_invoice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modem', function (Blueprint $table) {
            $table->dropColumn('address_to_invoice');
        });
    }
}
