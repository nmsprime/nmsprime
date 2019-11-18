<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateRealtyAddApartmentProperties extends BaseMigration
{
    protected $tablename = 'realty';

    /**
     * Run the migrations. Realty must have the same properties as an Apartment
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->boolean('connected');
            $table->string('connection_type')->nullable();
            $table->boolean('occupied');
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
            $table->dropColumn(['connected', 'connection_type', 'occupied']);
        });
    }
}
