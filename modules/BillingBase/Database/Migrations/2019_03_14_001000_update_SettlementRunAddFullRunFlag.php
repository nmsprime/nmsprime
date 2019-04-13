<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateSettlementRunAddFullRunFlag extends BaseMigration
{
    protected $tablename = 'settlementrun';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->boolean('fullrun')->unsigned();
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
            $table->dropColumn('fullrun');
        });
    }
}
