<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateContractAddPropertyManagementRelations extends BaseMigration
{
    protected $tablename = 'contract';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('realty_id');
            $table->integer('apartment_id');
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
