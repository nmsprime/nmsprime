<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateApartmentAddConnectionType extends BaseMigration
{
    protected $tablename = 'apartment';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('connection_type')->nullable();
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
            $table->dropColumn(['connection_type']);
        });
    }
}
