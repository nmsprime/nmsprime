<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateModemAddPropertyManagementRelations extends BaseMigration
{
    protected $tablename = 'modem';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->unsignedInteger('realty_id')->nullable();
            $table->unsignedInteger('apartment_id')->nullable();

            $table->foreign('realty_id')->references('id')->on('realty');
            $table->foreign('apartment_id')->references('id')->on('apartment');
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
            $table->dropForeign('modem_realty_id_foreign');
            $table->dropForeign('modem_apartment_id_foreign');

            $table->dropColumn(['realty_id', 'apartment_id']);
        });
    }
}
