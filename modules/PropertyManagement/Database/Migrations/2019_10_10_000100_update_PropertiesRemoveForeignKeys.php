<?php

use Illuminate\Database\Schema\Blueprint;

class UpdatePropertiesRemoveForeignKeys extends BaseMigration
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
            $table->dropForeign('contract_realty_id_foreign');
            $table->dropForeign('contract_apartment_id_foreign');
        });

        Schema::table('apartment', function (Blueprint $table) {
            $table->dropForeign('apartment_realty_id_foreign');
        });

        Schema::table('node', function (Blueprint $table) {
            $table->dropForeign('node_netelement_id_foreign');
        });

        Schema::table('realty', function (Blueprint $table) {
            $table->dropForeign('realty_node_id_foreign');
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
            $table->foreign('realty_id')->references('id')->on('realty');
            $table->foreign('apartment_id')->references('id')->on('apartment');
        });

        Schema::table('apartment', function (Blueprint $table) {
            $table->foreign('realty_id')->references('id')->on('realty');
        });

        Schema::table('node', function (Blueprint $table) {
            $table->foreign('netelement_id')->references('id')->on('netelement');
        });

        Schema::table('realty', function (Blueprint $table) {
            $table->foreign('node_id')->references('id')->on('node');
        });
    }
}
