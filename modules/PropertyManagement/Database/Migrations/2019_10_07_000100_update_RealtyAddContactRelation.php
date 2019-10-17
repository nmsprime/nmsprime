<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateRealtyAddContactRelation extends BaseMigration
{
    protected $tablename = 'realty';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->unsignedInteger('contact_id')->nullable();
            $table->unsignedInteger('contact_local_id')->nullable();
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
            $table->dropColumn(['contact_id', 'contact_local_id']);
        });
    }
}
