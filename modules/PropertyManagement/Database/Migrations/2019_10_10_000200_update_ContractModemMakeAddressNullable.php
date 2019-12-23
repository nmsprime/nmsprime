<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateContractModemMakeAddressNullable extends BaseMigration
{
    protected $tablename = 'contract';

    /**
     * Run the migrations.
     *
     * Contract and Modem address can now be null when address is inherited from Realty or apartment
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('street')->nullable()->change();
            $table->string('house_number', 8)->nullable()->change();
            $table->string('zip', 16)->nullable()->change();
            $table->string('city')->nullable()->change();
        });

        Schema::table('modem', function (Blueprint $table) {
            $table->string('street')->nullable()->change();
            $table->string('house_number', 8)->nullable()->change();
            $table->string('zip', 16)->nullable()->change();
            $table->string('city')->nullable()->change();
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
            $table->string('street')->change();
            $table->string('house_number', 8)->change();
            $table->string('zip', 16)->change();
            $table->string('city')->change();
        });

        Schema::table('modem', function (Blueprint $table) {
            $table->string('street')->change();
            $table->string('house_number', 8)->change();
            $table->string('zip', 16)->change();
            $table->string('city')->change();
        });
    }
}
