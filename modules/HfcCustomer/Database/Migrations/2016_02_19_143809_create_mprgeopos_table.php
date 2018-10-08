<?php

use Illuminate\Database\Schema\Blueprint;

/*
 * MPR: Modem Positioning Rule Geopos Table
 */
class CreateMprGeoposTable extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'mpr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mprgeopos', function (Blueprint $table) {
            $this->up_table_generic($table);

            // general
            $table->string('name');
            $table->integer('mpr_id');	// reference to mpr

            // geopos for rectangle
            $table->double('x');
            $table->double('y');

            $table->text('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mprgeopos');
    }
}
