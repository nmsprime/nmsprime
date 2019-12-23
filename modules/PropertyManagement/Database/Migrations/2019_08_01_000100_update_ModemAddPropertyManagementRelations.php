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
            $table->dropColumn(['realty_id', 'apartment_id']);
        });
    }
}
