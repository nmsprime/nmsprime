<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateItemIncreaseCount extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'item';

    /**
     * Run the migrations. Dont limit count to 256
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->unsignedInteger('count')->default(1)->change();
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
            // Note: Converting directly to tinyInteger throws an error
            // hint to convert to smallInt first was given here: https://github.com/laravel/framework/issues/8840
            $table->smallInteger('count')->unsignedTinyInteger('count')->default(1)->change();
        });
    }
}
