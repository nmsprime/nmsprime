<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateAccountingRecordAddRelation extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accountingrecord', function (Blueprint $table) {
            $table->unsignedInteger('settlementrun_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accountingrecord', function (Blueprint $table) {
            $table->dropColumn('settlementrun_id');
        });
    }
}
