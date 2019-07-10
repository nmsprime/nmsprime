<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateEnviaContractChangeTariffVariationType extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'enviacontract';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE nmsprime.enviacontract MODIFY COLUMN method VARCHAR(4)');    // change directly because of https://github.com/laravel/framework/issues/1186
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('tariff_id', 64)->nullable()->default(null)->change();
            $table->string('variation_id', 64)->nullable()->default(null)->change();
            /* $table->string('method')->default('SIP')->change(); */
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
            $table->integer('tariff_id')->unsigned()->nullable()->default(null)->change();
            $table->integer('variation_id')->unsigned()->nullable()->default(null)->change();
            /* $table->enum('method', ['MGCP', 'SIP'])->default('SIP')->change(); // no rollback; ENUM not supported and only used like string*/
        });
    }
}
