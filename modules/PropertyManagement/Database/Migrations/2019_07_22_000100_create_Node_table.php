<?php

use Illuminate\Database\Schema\Blueprint;

class CreateNodeTable extends BaseMigration
{
    protected $tablename = 'node';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->unsignedInteger('netelement_id')->nullable();
            $table->foreign('netelement_id')->references('id')->on('netelement');

            $table->string('name');
            $table->string('street');
            $table->string('house_nr');
            $table->string('zip');
            $table->string('city');

            $table->string('type')->nullable();             // signal type
            $table->boolean('headend');

            $table->string('description')->nullable();

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
