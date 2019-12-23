<?php

use Illuminate\Database\Schema\Blueprint;

class UpdatePropertyManagementSetForeignKeyForGroupContracts extends BaseMigration
{
    protected $tablename = 'realty';

    /**
     * Run the migrations. Add relation of realties to contract for a group contract
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('contract_id')->unsigned()->nullable();

            $table->dropColumn(['group_contract']);
        });

        Schema::table('contract', function (Blueprint $table) {
            $table->integer('contact_id')->unsigned()->nullable();

            $table->dropColumn(['realty_id']);
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
            $table->dropColumn(['contract_id']);

            $table->boolean('group_contract');
        });

        Schema::table('contract', function (Blueprint $table) {
            $table->dropColumn(['contact_id']);

            $table->integer('realty_id')->unsigned();
        });
    }
}
