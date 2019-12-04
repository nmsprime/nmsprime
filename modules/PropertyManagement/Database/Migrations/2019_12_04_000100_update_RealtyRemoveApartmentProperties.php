<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateRealtyRemoveApartmentProperties extends BaseMigration
{
    protected $tablename = 'realty';

    /**
     * Run the migrations. Revert the last migration as Realty must always have an Apartment
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn(['connected', 'connection_type', 'occupied']);
        });

        Schema::table('modem', function (Blueprint $table) {
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
            $table->boolean('connected');
            $table->string('connection_type')->nullable();
            $table->boolean('occupied');
        });

        Schema::table('modem', function (Blueprint $table) {
            $table->integer('realty_id')->unsigned();
        });
    }
}
