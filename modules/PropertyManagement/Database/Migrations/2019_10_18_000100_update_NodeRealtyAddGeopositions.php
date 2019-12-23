<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateNodeRealtyAddGeopositions extends BaseMigration
{
    protected $tablename = 'node';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->decimal('x', 11, 8)->nullable();
            $table->decimal('y', 11, 8)->nullable();
            $table->string('country_code')->nullable();
            $table->string('geocode_source')->nullable();
            $table->string('district')->nullable();
        });

        Schema::table('realty', function (Blueprint $table) {
            $table->decimal('x', 11, 8)->nullable();
            $table->decimal('y', 11, 8)->nullable();
            $table->string('geocode_source')->nullable();
            $table->string('country_code')->nullable();
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
            $table->dropColumn(['x', 'y', 'country_code', 'district', 'geocode_source']);
        });

        Schema::table('realty', function (Blueprint $table) {
            $table->dropColumn(['x', 'y', 'country_code', 'geocode_source']);
        });
    }
}
