<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateContractaddValueDate extends BaseMigration
{
    // name of the table to change
    protected $tablename = 'contract';

    /**
     * Run the migrations.
     * Because of new validation rules birthday can now be NULL.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->tinyInteger('value_date')->nullable();
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
            $table->dropColumn('value_date');
        });
    }
}
