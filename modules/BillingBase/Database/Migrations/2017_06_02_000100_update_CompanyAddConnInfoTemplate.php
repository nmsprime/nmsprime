<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateCompanyAddConnInfoTemplate extends BaseMigration
{
    protected $tablename = 'company';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('conn_info_template_fn');
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
            $table->dropColumn('conn_info_template_fn');
        });
    }
}
