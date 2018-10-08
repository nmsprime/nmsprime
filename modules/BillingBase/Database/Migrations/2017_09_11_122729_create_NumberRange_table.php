<?php

use Illuminate\Database\Schema\Blueprint;

class CreateNumberRangeTable extends BaseMigration
{
    protected $tablename = 'numberrange';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->string('name');
            $table->integer('start');
            $table->integer('end');
            $table->string('prefix');
            $table->string('suffix');
            $table->integer('costcenter_id');
            $table->enum('type', ['contract', 'invoice']);

            return parent::up();
        });

        $this->set_fim_fields(['name']);
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
