<?php

use Illuminate\Database\Schema\Blueprint;

class CreateApartmentTable extends BaseMigration
{
    protected $tablename = 'apartment';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->unsignedInteger('realty_id');
            $table->foreign('realty_id')->references('id')->on('realty');

            // $table->string('name')->nullable();
            $table->string('number')->nullable();
            $table->smallInteger('floor');
            $table->boolean('connected');
            $table->boolean('occupied');

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
