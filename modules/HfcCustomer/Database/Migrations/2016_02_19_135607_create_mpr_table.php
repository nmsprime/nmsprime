<?php

use Illuminate\Database\Schema\Blueprint;

/*
 * MPR: Modem Positioning Rule
 */
class CreateMprTable extends BaseMigration
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
        Schema::create('mpr', function (Blueprint $table) {
            $this->up_table_generic($table);

            // general
            $table->string('name');
            $table->integer('type');
            $table->text('value'); // fore direct coded entries
            $table->integer('tree_id');

            // prioritization
            $table->integer('prio');
            $table->integer('prio_id');
            $table->integer('prio_before_id');
            $table->integer('prio_after_id');

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
        Schema::drop('mpr');
    }
}
