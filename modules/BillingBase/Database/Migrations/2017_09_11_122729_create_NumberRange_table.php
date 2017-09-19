<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNumberRangeTable extends BaseMigration {

    protected $tablename = 'numberrange';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function(Blueprint $table)
        {
            $this->up_table_generic($table);

            $table->string('prefix');
            $table->string('suffix');
            $table->integer('start');
            $table->integer('end');
            $table->string('name');
            $table->integer('costcenter_id');
            $table->enum('type', ['contract', 'invoice']);
            $table->integer('last_generated_number');

            return parent::up();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tablename);
    }

}
