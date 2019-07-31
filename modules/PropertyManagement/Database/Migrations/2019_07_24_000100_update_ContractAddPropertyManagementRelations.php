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
            $table->unsignedInteger('realty_id')->nullable();
            $table->unsignedInteger('apartment_id')->nullable();

            $table->foreign('realty_id')->references('id')->on('realty')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartment')->onDelete('set null')->onUpdate('cascade');
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
            $table->dropForeign('contract_realty_id_foreign');
            $table->dropForeign('contract_apartment_id_foreign');

            $table->dropColumn(['realty_id', 'apartment_id']);
        });
    }
}
