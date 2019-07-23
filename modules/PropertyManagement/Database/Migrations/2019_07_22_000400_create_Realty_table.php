<?php

use Illuminate\Database\Schema\Blueprint;

class CreateRealtyTable extends BaseMigration
{
    protected $tablename = 'realty';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->integer('node_id')->nullable();

            $table->string('name')->nullable();
            $table->string('number')->nullable();

            $table->string('street');
            $table->string('house_nr');
            $table->string('zip');
            $table->string('city');

            $table->string('administration')->nullable();
            $table->string('expansion_degree')->nullable();
            $table->string('concession_agreement')->nullable();
            $table->date('agreement_from')->nullable();
            $table->date('agreement_to')->nullable();

            $table->date('last_restoration')->nullable();
            $table->boolean('group_contract');

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
